# Fellowship Application Retrieval from HUB — Workflow

Rendered diagrams live in `diagrams/` (Graphviz `.dot` sources + `.svg`/`.png` renders;
re-render with `dot -Tsvg <name>.dot -o <name>.svg`):

- `diagrams/hub_overview` — all channels and the shared HMAC auth model
- `diagrams/flow_a_applications` — applications retrieval & population
- `diagrams/flow_b_rec_letters` — recommendation letters retrieval

Data flow between the **Caller server** (local tenant, e.g. `view.med.cornell.edu`)
and the **Remote HUB server** (public tandem hub tenant instance, e.g. `view.online`).

Source files:

| Role | File |
|---|---|
| Caller trigger + Remote endpoints | `src/App/FellAppBundle/Controller/FellAppRetrievalController.php` |
| Caller logic (retrieve, populate, download) | `src/App/FellAppBundle/Util/FellAppImportPopulateHubUtil.php` |
| Rec-letter caller route + Remote endpoint | `src/App/FellAppBundle/Controller/FellAppHubRecomLetterController.php` |
| Cron entry point | `src/App/FellAppBundle/Command/CronCommand.php` (`cron:importfellapp`) |
| Manual trigger | `FellAppController::importAndPopulateAction` (`/populate-import`, `ROLE_FELLAPP_ADMIN`) |
| Reverse direction (Caller → HUB) | `FellAppTransferToHubUtil.php`, `TransferToHubCommand.php`, `FellAppTransferToHubController.php` |

---

## 1. Authentication model (both directions)

```
Caller and HUB share the same Institution.apiConnectionKey (plaintext, configured per side)

Caller side:
  apiHashConnectionKey = sha256(apiConnectionKey)
  X-Timestamp          = time()
  X-HMAC               = hash_hmac('sha256', 'fellapp-api:' + X-Timestamp, apiHashConnectionKey)

HUB side (authenticateHmac, FellAppImportPopulateHubUtil.php:1929):
  for each Institution with apiHashConnectionKey on HUB:
      expected = hash_hmac('sha256', 'fellapp-api:' + X-Timestamp, institution.apiHashConnectionKey)
      hash_equals(expected, X-HMAC)  ->  authenticated
  plus: |time() - X-Timestamp| <= 300s   (replay window)

API routes are PUBLIC_ACCESS (config/packages/security_access_control.yml:153-154);
authentication is entirely via the HMAC headers, not a session.
```

---

## 2. Flow A — Retrieve & populate applications (HUB → Caller)

### Triggers on the Caller

```
+-----------------------------------------------------------------------------+
| cron:importfellapp  (CronCommand.php:62)                                    |
|   if retrievalMethod == 'Dedicated public tandem hub server tenant instance'|
|       -> FellAppImportPopulateHubUtil::retrieveApplicationData()            |
|       -> FellAppImportPopulateHubUtil::retrieveRecommendationLetters()      |
|                                                                             |
| Manual: GET /fellowship-applications/populate-import  (FellAppController)   |
|   if retrievalMethod == hub  ->  redirect fellapp_retrieve_application_data |
|                                                                             |
| Direct: GET /fellowship-applications/retrieve-application-data              |
|   FellAppRetrievalController::retrieveApplicationDataAction  (line 51)      |
+-----------------------------------------------------------------------------+
```

### Sequence diagram

```mermaid
sequenceDiagram
    participant C as Caller server
    participant H as HUB server (view.online)

    C->>C: retrieveApplicationData()<br/>apiConnectionKey -> sha256 -> hash key<br/>maxid = MAX(remoteId)
    C->>H: GET {hubServerApiUrl}?maxid=N<br/>X-HMAC + X-Timestamp
    H->>H: authenticateHmac() + timestamp window
    H->>H: FellowshipApplication: id>maxid AND appStatus='active'
    H->>H: generateXlsxData() (~200-col horizontal sheet,<br/>doc URLs+hashes, apiHashConnectionGlobalKey)
    H-->>C: JSON {success, filename, xlsx_base64}
    C->>C: save xlsx -> private/Uploaded/fellapp/Spreadsheets/
    C->>C: populateFellappFromFile() -> createFellappFromRow() per row
    loop each document (photo, CV, cover letter, USMLE, reprimand, legal)
        C->>H: GET /fellowship-applications/download-application-file?document_hash=...
        H->>H: authenticateHmac(); Document by hash; getFullServerPath()
        H-->>C: JSON {file_base64, filename, ...}
        C->>C: save -> private/Uploaded/fellapp/{applicantsUploadPathFellApp}<br/>Document entity + thumbnails
    end
    C->>C: persist user + application, queue PDF report, send emails
```

### Caller side detail — `retrieveApplicationData()` (Util:93)

```
1. getInstitutionApiConnectionKey()            (Util:1987)
     exactly 1 institution must have apiConnectionKey, else error
2. maxid = MAX(CAST(remoteId AS INTEGER))      (Util:127-135)   <- incremental sync
3. remoteUrl = site setting 'hubServerApiUrl' + '?maxid=' + maxid
     default: https://view.online/fellowship-applications/download-application-data
4. GET remoteUrl  (HttpClient, verify_peer/verify_host = false)
5. Response 200 + success -> base64_decode(xlsx_base64)
6. Save xlsx to  private/Uploaded/fellapp/Spreadsheets/{filename}
7. populateFellappFromFile(filepath)           (Util:611)
8. Flash 'Populated N fellowship application(s)' -> redirect fellapp_home
```

### HUB side detail — `downloadApplicationDataAction` (Controller:78)

```
1. X-HMAC / X-Timestamp headers present -> authenticateHmac() -> |dt| <= 300s
2. fellapps = FellowshipApplication WHERE id > :maxid AND appStatus='active' ORDER BY id ASC
     (none -> 404 'No new FellowshipApplications')
3. generateXlsxData(fellapps)                  (Util:2041)
     per row: populateRow()                   (Util:2172)
       ID                = getFormId() = {id}_{instanceId}_{Y-m-d-H-i-s}
       originalAppId     = remote application id
       instanceId        = HUB site parameter
       apihashconnectionkey       = FellowshipSubspecialty.apiHashConnectionKey
       apihashconnectionglobalkey = GlobalFellowshipSpecialty.apiHashConnectionKey
       uploaded{Photo,CV,CoverLetter,USMLEScores,Reprimand,Legal}Url/Hash
       + all personal/address/exam/training/license/boardCert/reference fields
4. return JSON { success, filename, xlsx_base64 }
```

### Caller populate detail — `populateFellappFromFile` -> `createFellappFromRow` (Util:611, 682)

```
for each xlsx row (from row 2):
  googleFormId = column 'ID'; skip if empty or already exists (findOneByGoogleFormId)
  required: email + lastName + firstName; skip row if missing
  lookup: FellAppStatus 'active', EmploymentType 'Pathology Fellowship Applicant',
          userkeytype 'local-user', retrievalMethod site param

  specialty match: apihashconnectionglobalkey
      -> fellappUtil->findFellowshipSpeciatlyByApiHashKey()
      -> local FellowshipSubspecialty;  NO MATCH -> skip row (warning logged)

  user resolution chain (first hit wins):
      findOneByPrimaryPublicUserId(usernameCanonical)
      findOneByPrimaryPublicUserId(primaryPublicUserId)
      findOneByEmailCanonical / findUserByUserInfoEmail
      findUserByUsernameAsEmail / getUserByUserstr
      else: NEW locked User  (createdby='hubimport', password="",
                              + EmploymentStatus 'Pathology Fellowship Applicant')

  new FellowshipApplication(systemUser):
      appStatus=active, googleFormId, remoteId=originalAppId, retrievalMethod
      timestamps, start/end training period
      Examination (USMLE 1/2CK/2CS/3, ECFMG, COMLEX 1-3)
      downloadRemoteDocuments()   <- pulls 6 doc types from HUB (below)
      Locations: Present + Permanent (+Work if telephoneWork) w/ GeoLocation
      Citizenship + country via GenericTreeTransformer; DOB -> user credentials
      Training x9 (undergrad, grad, medical, residency, gme1-2, otherExperience1-3)
      StateLicense x2, BoardCertification x3, Reference x3 (with recLetterHashId)
      reprimand/lawsuit flags, honors, publications, memberships, signature

  persist + flush
  remove 'Fellowship Application Creation Failed' attempt log
  log  'Fellowship Application Created'
  fellapp_reportgenerator->addFellAppReportToQueue(app id)   <- PDF queued
  email: applicant confirmation (confirmationEmailFellApp/Subject/Body params)
  email: directors+coordinators via sendConfirmationEmailsOnApplicationPopulation
  email: rec-letter invitations via sendInvitationEmailsToReferences
```

### Remote document download — `downloadRemoteDocuments` / `downloadFileFromRemote` (Util:1119, 1324)

```
For each of 6 configured doc types (urlField + hashField + attach method):
  photo -> addAvatar,  CV -> addCv,  cover letter -> addCoverLetter,
  USMLE -> examination.addScore,  reprimand -> addReprimandDocument,
  legal -> addReprimandDocument

  skip if url/hash cell empty
  dedup: Document.findOneByDocumentHash(fileHash) -> attach existing, skip download
  GET  {hubBaseUrl}/fellowship-applications/download-application-file?document_hash=HASH
       + X-HMAC/X-Timestamp  (fresh HMAC per request)
  HUB: downloadApplicationFileAction (Controller:761)
       auth -> Document by hash -> getFullServerPath() -> base64
  save: private/Uploaded/fellapp/{applicantsUploadPathFellApp}/{ts}ID{hash}.{ext}
  new Document (documentHash, uploadDirectory relative to private/,
                DocumentTypeList via GenericTreeTransformer), flush, thumbnails
```

---

## 3. Flow B — Recommendation letters (HUB → Caller)

```mermaid
sequenceDiagram
    participant C as Caller server
    participant H as HUB server

    C->>C: retrieveRecommendationLetters()<br/>References WHERE fellapp.remoteId NOT NULL<br/>AND recLetterReceived false/null<br/>AND recLetterHashId set
    C->>H: GET /fellowship-applications/send-recommendation-letters?hashids=a,b,c<br/>X-HMAC + X-Timestamp
    H->>H: authenticateHmac()
    H->>H: per hashId: Reference by recLetterHashId<br/>-> getRecentReferenceLetter() -> getFullServerPath()
    H->>H: reference.setRecLetterReceived(true); flush
    H-->>C: JSON {letters: [{hashId, documentData(base64), filename, hash}]}
    C->>C: per letter: Reference by recLetterHashId (skip if received)<br/>save file -> private/Uploaded/fellapp/documents/{hashId}.pdf<br/>new Document, attach, setRecLetterReceived(true), flush
```

- Caller trigger: cron `cron:importfellapp` (hub method) or `GET /fellowship-applications/retrieve-recommendation-letters` (FellAppHubRecomLetterController:368).
- HUB endpoint: `sendRecommendationLettersAction` (FellAppHubRecomLetterController:385). No `hashids` param -> returns *all* letters with hashIds.
- References upload letters on the HUB via the public page `/fellowship-applications/submit-a-letter-of-recommendation` (site param #6).

---

## 4. Flow C — Specialty parameters (Caller → HUB, reverse direction)

Not part of retrieval, but shares the same HMAC channel:

```
TransferToHubCommand / FellAppTransferToHubController
  -> FellAppTransferToHubUtil::transferParametersToHub()   (line 57)
     POST {hubServerApiUrl with download-application-data -> receive-specialty-parameters}
     body: specialtyParameters[] = {id, specialtyHashConnectionKey, name,
           institution, duration, seasonYearStart/End, acceptingApplication,
           coordinators[], directors[]}
  HUB: FellAppTransferToHubController::/receive-specialty-parameters (line 72)
```

Purpose: the HUB learns which specialties each caller accepts applications for
(acceptingApplication, season dates, contact users) so applicants on the public
portal pick from the caller-managed catalog.

---

## 5. Error / edge cases

| Case | Behavior |
|---|---|
| No `apiConnectionKey` institution on Caller | `{success:false, 'the API Connection Key is not configured'}`, status 500 |
| `hubServerApiUrl` site param empty | `{success:false, 'fellappRemoteServerUrl is not defined...'}` |
| HMAC fails on HUB | 401 `Invalid HMAC authentication` |
| Timestamp outside 5-min window | 401 `Request timestamp expired` / 403 `too old` |
| No new apps (id > maxid) | 404 `No new FellowshipApplications found` |
| Specialty hash not matched locally | row skipped, warning logged (not counted as populated) |
| googleFormId already imported | row skipped (idempotent re-runs) |
| Missing email/lastName/firstName | row skipped, warning logged |
| Document hash already in local DB | file not re-downloaded; existing Document attached |
| Exception in `createFellappFromRow` | row skipped, error logged, loop continues |
| `testing=true` | row parsed, nothing persisted |

## 6. Configuration prerequisites

Caller server:
- Site setting `hubServerApiUrl` = `https://<hub>/fellowship-applications/download-application-data`
- Exactly one Institution with `apiConnectionKey` set
- `retrievalMethod` = `Dedicated public tandem hub server tenant instance`
- `localInstitutionFellApp`, `applicantsUploadPathFellApp`, confirmation email params
- Local `FellowshipSubspecialty` rows whose `apiConnectionKey` sha256-matches the
  HUB's `GlobalFellowshipSpecialty.apiHashConnectionKey` values

HUB server:
- Same `apiConnectionKey` on the corresponding Institution(s)
- `instanceId` site param
- Both API routes reachable publicly (PUBLIC_ACCESS + HMAC)


Notes:
Applications: the HUB never learns whether population succeeded.
The sync state lives entirely on the caller — the next request sends
?maxid=N where N = MAX(CAST(remoteId AS INTEGER)) on fellapp_fellowshipapplication (Util:127-135).
The HUB just returns id > maxid again.
Caller-side success is implicit: the app now exists with that remoteId, so it's excluded next run.

Recommendation letters: the comment at FellAppImportPopulateHubUtil.php:563 mentions
a "separate API confirmation call" — it's not implemented.
Instead the HUB marks reference->setRecLetterReceived(true) itself at send-time
in sendRecommendationLettersAction (lines 454, 487) —
i.e., "received" means "sent to caller", not "attached on caller".
The caller then marks its own Reference copy received after attaching the Document.
