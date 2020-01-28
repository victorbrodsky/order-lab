1) Rename column name with 00000
ALTER TABLE transres_invoice RENAME COLUMN oid00000 TO oid;
ALTER TABLE scan_message RENAME COLUMN oid00000 TO oid;
ALTER TABLE transres_project RENAME COLUMN oid00000 TO oid;
ALTER TABLE transres_request RENAME COLUMN oid00000 TO oid;

#2) Update unique indexes
CREATE INDEX idx_ea22b84e63b59e5c
    ON public.scan_calllogentrymessage_patientlist USING btree
    (calllogentrymessage_id ASC NULLS LAST)
    TABLESPACE pg_default;

CREATE INDEX idx_d312ef9263b59e5c
    ON public.scan_calllogentrymessage_entrytag USING btree
    (calllogentrymessage_id ASC NULLS LAST)
    TABLESPACE pg_default;

CREATE INDEX idx_d267b39c33f7837
    ON public.calllog_calllogentrymessage_document USING btree
    (document_id ASC NULLS LAST)
    TABLESPACE pg_default;

CREATE INDEX idx_7f071c8fd6e2fadc
    ON public.scan_message_encounter USING btree
    (encounter_id ASC NULLS LAST)
    TABLESPACE pg_default;

CREATE INDEX idx_cf568530a40f1370
    ON public.scan_message_accession USING btree
    (accession_id ASC NULLS LAST)
    TABLESPACE pg_default;

CREATE INDEX idx_5eb89a4de9ed820c
    ON public.scan_message_block USING btree
    (block_id ASC NULLS LAST)
    TABLESPACE pg_default;

CREATE INDEX idx_6be23a97726d9566
    ON public.scan_messagecategory_formnode USING btree
    (formnode_id ASC NULLS LAST)
    TABLESPACE pg_default;

#3) Replace entitynamespace Oleg to App in Document.php, FosComment.php, GeneralEntity.php, Logger.php and all lists (enough to execute SQL1 and SQL2))
UPDATE public.user_logger SET entitynamespace = REPLACE(entitynamespace,'Oleg','App');
UPDATE public.user_foscomment SET entitynamespace = REPLACE(entitynamespace,'Oleg','App');
UPDATE public.user_generalentity SET entitynamespace = REPLACE(entitynamespace,'Oleg','App');
UPDATE public.user_document SET entitynamespace = REPLACE(entitynamespace,'Oleg','App');

4) Replace entitynamespace Oleg to App in others
############## SQL1) Update all tables with column='entitynamespace' ################
DO
$do$
DECLARE
    rec record;
BEGIN
    FOR rec IN
        SELECT table_schema, table_name, column_name
        FROM information_schema.columns
        WHERE column_name = 'entitynamespace'
    LOOP
        EXECUTE FORMAT(
          $$
            UPDATE %I.%I SET entitynamespace = replace(entitynamespace,'Oleg','App') WHERE entitynamespace IS NOT NULL;
          $$,
          rec.table_schema, rec.table_name
        );
    END LOOP;
END;
$do$;
############# SQL2) Update all tables with column='receivedvalueentitynamespace' #################
DO
$do$
DECLARE
    rec record;
BEGIN
    FOR rec IN
        SELECT table_schema, table_name, column_name
        FROM information_schema.columns
        WHERE column_name = 'receivedvalueentitynamespace'
    LOOP
        EXECUTE FORMAT(
          $$
            UPDATE %I.%I SET receivedvalueentitynamespace = replace(receivedvalueentitynamespace,'Oleg','App') WHERE receivedvalueentitynamespace IS NOT NULL;
          $$,
          rec.table_schema, rec.table_name
        );
    END LOOP;
END;
$do$;
##############################


#5) Update siteparameters
UPDATE public.user_siteparameters SET connectionchannel='http';

UPDATE public.user_siteparameters SET emailcriticalerror=false;
UPDATE public.user_siteparameters SET p12keypathfellapp='/opt/order-lab/orderflex/src/App/FellAppBundle/Util/FellowshipApplication-f1d9f98353e5.p12';

UPDATE public.user_siteparameters SET pdftkfilenamefellapp='/usr/bin';
UPDATE public.user_siteparameters SET libreofficeconverttopdffilenamefellapplinux='soffice';
UPDATE public.user_siteparameters SET libreofficeconverttopdfargumentsdfellapplinux='--headless -convert-to pdf -outdir';
UPDATE public.user_siteparameters SET pdftkpathfellapplinux='/usr/bin';
UPDATE public.user_siteparameters SET pdftkfilenamefellapplinux='pdftk';
UPDATE public.user_siteparameters SET pdftkargumentsfellapplinux='###inputFiles### cat output ###outputFile### dont_ask';
UPDATE public.user_siteparameters SET gspathfellapplinux='/usr/bin';
UPDATE public.user_siteparameters SET gsfilenamefellapplinux='ghostscript';
UPDATE public.user_siteparameters SET gsargumentsfellapplinux='-q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=###outputFile### -c .setpdfwrite -f ###inputFiles###';
UPDATE public.user_siteparameters SET wkhtmltopdfpathlinux='/usr/bin/xvfb-run wkhtmltopdf';
UPDATE public.user_siteparameters SET phantomjslinux='/opt/phantomjs-2.1.1-linux-x86_64/bin/phantomjs';
UPDATE public.user_siteparameters SET rasterizelinux='/opt/order-lab/orderflex/src/App/UserdirectoryBundle/Util/rasterize.js';
UPDATE public.user_siteparameters SET pathmetaphone='/opt/Metaphone3/metaphone3.php';
UPDATE public.user_siteparameters SET networkdrivepath='';
UPDATE public.user_siteparameters SET libreofficeconverttopdfpathfellapplinux='/usr/bin';

UPDATE public.user_siteparameters SET allowpopulatefellapp=false;
UPDATE public.user_siteparameters SET mailerspool=false;
UPDATE public.user_siteparameters SET environment='test';

6) update trancated table name
user_organizationalgroupdefault_permittedinstitutionalphiscope (63 chars max)
user_organizationalGroupDefault_permittedInstitutionalPHIScope





