from web_automation import WebAutomation
from users import Users
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import Select
from selenium.webdriver.support import expected_conditions as EC
#from selenium.webdriver.common.action_chains import ActionChains
from selenium.common.exceptions import TimeoutException
from selenium.common.exceptions import NoAlertPresentException
from selenium.webdriver.common.action_chains import ActionChains
import time
import datetime
import random
import json
import os
import requests
#from datetime import date
#from dateutil.relativedelta import relativedelta
#from selenium.webdriver.support.expected_conditions import visibility_of_all_elements_located
from selenium.common.exceptions import NoSuchElementException
from selenium.common.exceptions import JavascriptException




class FellApp:
    def __init__(self, automation):
        self.automation = automation
        self.users = Users(automation)
        self.existing_users = self.users.get_existing_users(with_admin=True)

    def get_references(self):
        references = []
        references.append({
            'firstName': 'Helena',
            'lastName': 'Markovic',
            'displayName': 'Helena Markovic',
            'email': 'Helena.Markovic@example.com',
        })
        references.append({
            'firstName': 'Kazuki',
            'lastName': 'Tanaka',
            'displayName': 'Kazuki Tanaka',
            'email': 'Kazuki.Tanaka@example.com',
        })
        references.append({
            'firstName': 'Gregory',
            'lastName': 'Ashford',
            'displayName': 'Gregory Ashford',
            'email': 'Gregory.Ashford@example.com',
        })
        references.append({
            'firstName': 'Amitabh',
            'lastName': 'Banerjee',
            'displayName': 'Amitabh Banerjee',
            'email': 'Amitabh.Banerjee@example.com',
        })
        references.append({
            'firstName': 'Mei-Ling',
            'lastName': 'Zhou',
            'displayName': 'Mei-Ling Zhou',
            'email': 'Mei-Ling.Zhou@example.com',
        })
        references.append({
            'firstName': 'Sylvia',
            'lastName': 'Marwood',
            'displayName': 'Sylvia Marwood',
            'email': 'Sylvia.Marwood@example.com',
        })
        references.append({
            'firstName': 'Adewale',
            'lastName': 'Okonjo',
            'displayName': 'Adewale Okonjo',
            'email': 'Adewale.Okonjo@example.com',
        })
        references.append({
            'firstName': 'Sofia',
            'lastName': 'Mendez',
            'displayName': 'Sofia Mendez',
            'email': 'Sofia.Mendez@example.com',
        })
        references.append({
            'firstName': 'Lucien',
            'lastName': 'Dubois',
            'displayName': 'Lucien Dubois',
            'email': 'Lucien.Dubois@example.com',
        })
        return references

    def get_comments(self):
        comments = []
        comments.append("I enjoyed talking to this outstanding candidate and I believe this would be a valuable addition to our program!")
        comments.append(
            "Wonderful candidate who did amazing work on the immunobiology research project. Looking forward to working together!")
        comments.append(
            "Interesting candidate. Shows leadership potential and strong interest in research.")
        comments.append("Impressive communication skills and a clear passion for advancing clinical research.")
        comments.append("Demonstrated strong analytical thinking and a thoughtful approach to patient care.")
        comments.append("Highly motivated individual with a collaborative spirit and excellent academic background.")
        comments.append("Engaged well during the interview and asked insightful questions about program structure.")
        comments.append("Shows great promise in translational medicine and has a compelling long-term vision.")
        comments.append(
            "Candidate brings a unique interdisciplinary perspective that could enrich our collaborative research efforts.")
        comments.append("Strong academic foundation and a clear commitment to advancing healthcare through innovation.")
        comments.append(
            "Exhibited maturity and professionalism throughout the interview, with well-articulated career goals.")
        comments.append(
            "Demonstrated deep curiosity and a proactive approach to learning—an asset to any research team.")
        comments.append("Has a compelling personal story that aligns well with the mission and values of our program.")
        return comments

    def get_fell_apps(self):
        fellapps = []
        fellapps.append({
            'type': '1',  # 'Clinical Informatics'
            'firstName': 'Lisa',
            'lastName': 'Chen',
            'displayName': 'Lisa Chen', #'Joe Simpson',
            'email': 'cinava@yahoo.com',
            'usmlestep1': 'Pass',
            'usmlestep2': 253,
            'usmlestep3': 242,
            'specialty': 'Molecular Genetic Pathology',
            'photo': 'lisa-chen.jpeg',
            'medschool': 'Johns Hopkins University School of Medicine',
            #AP/CP Pathology — Massachusetts General Hospital / Harvard Medical School
            #AP/CP, school, year (current + 1), city, state, country
            'residency_specialty': ['AP/CP', 'Massachusetts General Hospital / Harvard Medical School', 'Boston', 'Massachusetts', 'United States'],
            #Surgical Pathology Fellowship — Memorial Sloan Kettering Cancer Center
            #specialty, school, year (current + 2), city, state, country
            'fellowship_specialty': ['Surgical Pathology Fellowship', 'Memorial Sloan Kettering Cancer Center', 'New-York', 'New-York', 'United States'],
            'interview_date': '09/12/2026',
            'interview_score': '4.3',
            'comment': "I enjoyed talking to this outstanding candidate and I believe this would be a valuable addition to our program!"
        })
        fellapps.append({
            'type': '1',  # 'Clinical Informatics'
            'firstName': 'Jessica',
            'lastName': 'Santiago',
            'displayName': 'Jessica Santiago', #'Soleil Teresia',
            'email': 'cinava@yahoo.com',
            'usmlestep1': 'Pass',
            'usmlestep2': 247,
            'usmlestep3': 238,
            'specialty': 'Clinical Informatics',
            'photo': 'jessica-santiago.jpeg',
            'medschool': 'Washington University School of Medicine in St. Louis',
            'residency_specialty': ['AP', 'University of California', 'San Francisco', 'California', 'United States'],
            'fellowship_specialty': ['Breast Pathology Fellowship', 'Mayo Clinic', 'Rochester', 'New-York', 'United States'],
            'interview_date': '14/12/2026',
            'interview_score': '3.9',
            'comment': "Wonderful candidate who did amazing work on the immunobiology research project. Looking forward to working together!"
        })
        fellapps.append({
            'type': '1',  # 'Clinical Informatics'
            'firstName': 'Peter',
            'lastName': 'Neon',
            'displayName': 'Peter Neon', #'Haides Neon',
            'email': 'cinava@yahoo.com',
            'usmlestep1': 'Pass',
            'usmlestep2': 258,
            'usmlestep3': 244,
            'specialty': 'Gastrointestinal Pathology',
            'photo': 'peter-neon.jpeg',
            'medschool': 'University of Pennsylvania Perelman School of Medicine',
            'residency_specialty': ['CP', 'Stanford University Medical Center', 'Stanford', 'California', 'United States'],
            'fellowship_specialty': ['Hematopathology Fellowship', 'MD Anderson Cancer Center', 'Houston', 'Texas', 'United States'],
            'interview_date': '17/12/2026',
            'interview_score': '4.2',
            'comment': "Interesting candidate. Shows leadership potential and strong interest in research."
        })

        return fellapps

    def configs(self, max_count=0, batch_size=3):
        fellapp_names = [
            "Blood Banking and Transfusion Medicine",
            "Clinical Chemistry",
            "Clinical Informatics",
            "Cytopathology",
            "Gastrointestinal Pathology",
            "Dermatopathology",
            #"Genitourinary and Renal Pathology",
            "Genitourinary Pathology",
            "Renal Pathology",
            #"Gynecologic and Breast Pathology",
            "Breast Pathology",
            "Gynecologic Pathology",
            "Head and Neck Pathology",
            "Hematopathology",
            "Histocompatibility and Immunogenetics",
            "Laboratory Genetics and Genomics",
            "Liver and GI Pathology",
            "Medical and Public Health Microbiology",
            "Molecular Genetic Pathology",
            "Neuropathology",
            "Pediatric Pathology",
            "Surgical Pathology"
        ]

        # Get users first to avoid multiple calls
        users = self.users.get_users()
        
        # Store original automation instance
        original_automation = self.automation
        original_driver = original_automation.get_driver()
        
        count = 0
        processed_count = 0
        
        try:
            # Process in batches
            for i in range(0, len(fellapp_names), batch_size):
                batch = fellapp_names[i:i + batch_size]
                print(f"\n--- Processing batch {i//batch_size + 1} of {(len(fellapp_names) + batch_size - 1) // batch_size} ---")
                
                # Create a new WebDriver instance for each batch
                batch_automation = WebAutomation(
                    baseurl=original_automation.baseurl,
                    run_by_symfony_command=original_automation.run_by_symfony_command
                )
                batch_automation.login_to_site()
                self.automation = batch_automation
                
                try:
                    #max_count = 3 #testing
                    for fellapp_name in batch:
                        try:
                            time.sleep(3)
                            print(f"Processing {fellapp_name}...")
                            self.config_single_more(fellapp_name, users)
                            count += 1
                            processed_count += 1
                            print(f"✓ Completed {fellapp_name} ({processed_count}/{len(fellapp_names)})")
                            
                            if max_count > 0 and count >= max_count:
                                return
                                
                        except Exception as e:
                            print(f"Error processing config_single_more for {fellapp_name}: {str(e)}")
                            continue
                            
                finally:
                    # Clean up batch resources
                    try:
                        batch_automation.quit_driver()
                    except:
                        pass
                    del batch_automation
                    self.automation = None
                    
        finally:
            # Restore original automation instance
            self.automation = original_automation

    def config_single_more(self, fellapp_name, users):
        driver = self.automation.get_driver()
        fellowship_type_url = self.automation.baseurl.rstrip('/') + '/' + "fellowship-applications/fellowship-types-settings".lstrip('/')
        driver.get(fellowship_type_url)
        time.sleep(3)

        #wait = WebDriverWait(driver, 10)

        try:
            # Try to find the element
            #fellowship_type = driver.find_element("xpath", "//h4/a[contains(text(), 'Clinical Informatics')]")
            fellowship_type = driver.find_element("xpath", f"//h4/a[contains(text(), '{fellapp_name}')]")
            #print("Element found!")
            # You can perform actions on the element here
            fellowship_type.click()
            time.sleep(3)

            ###############################
            #####    Add director     #####
            ###############################
            try:
                # director = users[3]
                director = users[random.randint(3, len(users) - 1)]
                print(f"config_single_more: {fellapp_name} director: {director['displayName']}")
                director_user_id = self.existing_users[director['displayName']]
                print(f"director {director['displayName']} User ID: {director_user_id}")
                script = f"""
                                $("#s2id_oleg_fellappbundle_fellowshipSubspecialty_directors").select2('val','{director_user_id}');
                            """
                driver.execute_script(script)
                time.sleep(1)

                print(f"config_single_more: {fellapp_name} director added: {director['displayName']}")
            except Exception as e:
                print(f"config_single_more: unable to set director {director['displayName']} for {fellapp_name}: {e}")

            ###############################
            #####   Add coordinator  #####
            ###############################
            try:
                # Select 2 random coordinators from users[3:] (adjust as needed)
                selected_coordinators = random.sample(users[3:], 2)

                coordinator_ids = []
                for coordinator in selected_coordinators:
                    display_name = coordinator['displayName']
                    print(f"config_single_more: {fellapp_name} coordinator: {display_name}")
                    coordinator_user_id = self.existing_users.get(display_name)

                    if not coordinator_user_id:
                        print(f"Skipping unknown coordinator: {display_name}")
                        continue

                    coordinator_ids.append(coordinator_user_id)
                    print(f"✓ Coordinator {display_name} User ID: {coordinator_user_id}")

                if coordinator_ids:
                    # Inject all selected coordinator IDs into the Select2 field
                    script = f"""
                                $("#s2id_oleg_fellappbundle_fellowshipSubspecialty_coordinators")
                                    .select2('val', {json.dumps(coordinator_ids)});
                            """
                    driver.execute_script(script)
                    time.sleep(1)
                    print(f"✓ {fellapp_name}: {len(coordinator_ids)} coordinator(s) added.")
                else:
                    print(f"No valid coordinators found for {fellapp_name}.")

            except Exception as e:
                print(f"config_single_more: error setting coordinators for {fellapp_name}: {e}")

            ###############################
            #####   Add interviewers  #####
            ###############################
            try:
                # Select 2 random interviewers from users[3:] (adjust as needed)
                selected_interviewers = random.sample(users[3:], 2)

                interviewer_ids = []
                for interviewer in selected_interviewers:
                    display_name = interviewer['displayName']
                    print(f"config_single_more: {fellapp_name} interviewer: {display_name}")
                    interviewer_user_id = self.existing_users.get(display_name)

                    if not interviewer_user_id:
                        print(f"Skipping unknown interviewer: {display_name}")
                        continue

                    interviewer_ids.append(interviewer_user_id)
                    print(f"✓ Interviewer {display_name} User ID: {interviewer_user_id}")

                #Add director
                interviewer_ids.append(director_user_id)

                if interviewer_ids:
                    # Inject all selected interviewer IDs into the Select2 field
                    script = f"""
                        $("#s2id_oleg_fellappbundle_fellowshipSubspecialty_interviewers")
                            .select2('val', {json.dumps(interviewer_ids)});
                    """
                    driver.execute_script(script)
                    time.sleep(1)
                    print(f"✓ {fellapp_name}: {len(interviewer_ids)} interviewer(s) added.")
                else:
                    print(f"No valid interviewers found for {fellapp_name}.")

            except Exception as e:
                print(f"config_single_more: error setting interviewers for {fellapp_name}: {e}")

            ###############################
            ##### Click Update button #####
            ###############################
            time.sleep(3)
            try:
                update_button = WebDriverWait(driver, 10).until(
                    EC.presence_of_element_located((By.CSS_SELECTOR, "button.btn.btn-warning"))
                )
                update_button.click()
                print(f"config_single_more: after click Update button for {fellapp_name}")
                time.sleep(3)
                #driver.execute_script("arguments[0].scrollIntoView();", update_button)
                #driver.save_screenshot("configs_after_click_btn-warning.png")
                #for entry in driver.get_log('browser'):
                #    print(entry)
            except NoSuchElementException as e:
                print(
                    f"config_single_more: error in clicking button for {fellapp_name}. NoSuchElementException: {e}")

            #testing
            #return
        except NoSuchElementException as e:
            # create new fellowship type "Clinical Informatics"
            print(f"config_single_more: error in creating coordinator, director, interviewer for {fellapp_name}. NoSuchElementException: {e}")

    def set_site_settings(self):
        # Set fellowship start/end dates
        # https://view.online/c/demo-institution/demo-department/fellowship-applications/settings/specific-site-parameters/edit-page/
        driver = self.automation.get_driver()
        url = self.automation.baseurl.rstrip('/') + '/' + "fellowship-applications/settings/specific-site-parameters/edit-page/".lstrip('/')
        driver.get(url)

        time.sleep(3)

        start_date_month = driver.find_element(By.ID, "oleg_fellappbundle_fellappsiteparameter_fellappAcademicYearStart_month")
        time.sleep(1)
        select = Select(start_date_month)
        select.select_by_value("4")  # Since April has a value of "4"

        start_date_day = driver.find_element(By.ID, "oleg_fellappbundle_fellappsiteparameter_fellappAcademicYearStart_day")
        time.sleep(1)
        select = Select(start_date_day)
        select.select_by_value("1")
        time.sleep(3)

        # start_date_year = driver.find_element(By.ID, "oleg_userdirectorybundle_siteparameters_academicYearStart_year")
        # time.sleep(1)
        # select = Select(start_date_year)
        # select.select_by_value("2025")
        # time.sleep(3)

        end_date_month = driver.find_element(By.ID,
                                             "oleg_fellappbundle_fellappsiteparameter_fellappAcademicYearEnd_month")
        time.sleep(1)
        select = Select(end_date_month)
        select.select_by_value("3")  # Since March has a value of "3"

        end_date_day = driver.find_element(By.ID,
                                             "oleg_fellappbundle_fellappsiteparameter_fellappAcademicYearEnd_day")
        time.sleep(1)
        select = Select(end_date_day)
        select.select_by_value("31")
        time.sleep(3)

        #oleg_fellappbundle_fellappsiteparameter_acceptedEmailSubject
        field = driver.find_element(By.ID, "oleg_fellappbundle_fellappsiteparameter_acceptedEmailSubject")
        time.sleep(1)
        field.send_keys('Congratulations on your acceptance to the [[FELLOWSHIP TYPE]] [[START YEAR]] fellowship at Weill Cornell Medicine')
        time.sleep(1)

        #oleg_fellappbundle_fellappsiteparameter_acceptedEmailBody
        field = driver.find_element(By.ID, "oleg_fellappbundle_fellappsiteparameter_acceptedEmailBody")
        time.sleep(1)
        field.send_keys(
            "Dear [[APPLICANT NAME]],\n"
            "We are looking forward to having you join us as a [[FELLOWSHIP TYPE]] fellow in [[START YEAR]]!\n"
            "Sincerely,\n"
            "[[DIRECTOR]]"
        )
        time.sleep(1)

        #oleg_fellappbundle_fellappsiteparameter_rejectedEmailSubject
        field = driver.find_element(By.ID, "oleg_fellappbundle_fellappsiteparameter_rejectedEmailSubject")
        time.sleep(1)
        field.send_keys(
            "Thank you for applying to the [[FELLOWSHIP TYPE]] [[START YEAR]] fellowship at Weill Cornell Medicine"
        )
        time.sleep(1)

        #oleg_fellappbundle_fellappsiteparameter_rejectedEmailBody
        field = driver.find_element(By.ID, "oleg_fellappbundle_fellappsiteparameter_rejectedEmailBody")
        time.sleep(1)
        field.send_keys(
            "Dear [[APPLICANT NAME]],\n\n"
            "Thank you for your interest in the [[FELLOWSHIP TYPE]] Fellowship ([[START YEAR]]) in our Department.\n\n"
            "We deeply regret to inform you that we will not be able to offer you the Fellowship. "
            "We had several excellent applicants, including yourself, for the solitary position, and we made this difficult decision "
            "after a comprehensive review of all applications and interviews of some candidates.\n\n"
            "We wish you every success in your career.\n\n"
            "Sincerely,\n"
            "[[DIRECTOR]]"
        )
        time.sleep(1)

        self.automation.click_button_by_id("oleg_fellappbundle_fellappsiteparameter_save")
        time.sleep(3)
        print("fellappAcademicYear Start/End dates populated")

    def create_fellapps(self,max_count=0):
        users = self.users.get_users()
        comments = self.get_comments()
        fellapps = self.get_fell_apps()
        references = self.get_references()
        count = 0
        if max_count > 0:
            fellapps = fellapps[:max_count]
        for fellapp in fellapps:
            self.create_single_fellapp(count,fellapp,users,comments,references)
            count = count + 3
            #break #enable for test run only one

    def create_single_fellapp(self, count, fellapp, users, comments, references):
        driver = self.automation.get_driver()
        url = self.automation.baseurl.rstrip('/') + '/' + "fellowship-applications/new/".lstrip('/')
        #url = "http://127.0.0.1/fellowship-applications/new/"
        driver.get(url)
        time.sleep(1)

        displayName = fellapp["displayName"]
        print(f"Start submitting fellapp for {displayName}")

        #Create a new fellapp https://view.online/c/demo-institution/demo-department/fellowship-applications/new/
        #print("create new fellowship application")

        #### testing
        if 0:
            applicant_data_element = driver.find_element(By.CSS_SELECTOR,
                                                         "h4.panel-title > a[href='#uploads']")
            applicant_data_element.click()
            time.sleep(3)
            self.add_file(fellapp["photo"]) #("Jessica-Santiago.jpeg")
            exit()
        #### testing

        applicant_data_element = driver.find_element(By.CSS_SELECTOR,
                                                     "h4.panel-title > a[href='#fellowshipApplicantData']")
        applicant_data_element.click()
        time.sleep(3)

        self.automation.select_option(
            "s2id_oleg_fellappbundle_fellowshipapplication_fellowshipSubspecialty", "CSS_SELECTOR",
            "#select2-drop .select2-input",
            #"Clinical Informatics"
            fellapp["specialty"]
        )
        time.sleep(3)

        #oleg_fellappbundle_fellowshipapplication_user_infos_0_firstName
        first_name = driver.find_element(By.ID, "oleg_fellappbundle_fellowshipapplication_user_infos_0_firstName")
        first_name.send_keys(fellapp["firstName"])
        #oleg_fellappbundle_fellowshipapplication_user_infos_0_lastName
        last_name = driver.find_element(By.ID, "oleg_fellappbundle_fellowshipapplication_user_infos_0_lastName")
        last_name.send_keys(fellapp["lastName"])
        #oleg_fellappbundle_fellowshipapplication_user_infos_0_email
        email = driver.find_element(By.ID, "oleg_fellappbundle_fellowshipapplication_user_infos_0_email")
        email.send_keys(fellapp["email"])

        signature = driver.find_element(By.ID, "oleg_fellappbundle_fellowshipapplication_signatureName")
        signature.send_keys(fellapp["displayName"])

        today = datetime.date.today().strftime("%m-%d-%Y")
        signature_date = driver.find_element(By.ID, "oleg_fellappbundle_fellowshipapplication_signatureDate")
        signature_date.clear()
        signature_date.send_keys(today)
        time.sleep(5)

        #Open National Board section
        applicant_data_element = driver.find_element(By.CSS_SELECTOR, "h4.panel-title > a[href='#nationalBoards']")
        applicant_data_element.click()
        time.sleep(3)

        #USMLE Step 1
        signature = driver.find_element(By.ID, "oleg_fellappbundle_fellowshipapplication_examinations_0_USMLEStep1Score")
        signature.send_keys(fellapp["usmlestep1"])
        time.sleep(1)

        signature = driver.find_element(By.ID, "oleg_fellappbundle_fellowshipapplication_examinations_0_USMLEStep2CKScore")
        signature.send_keys(fellapp["usmlestep2"])

        signature = driver.find_element(By.ID,
                                        "oleg_fellappbundle_fellowshipapplication_examinations_0_USMLEStep3Score")
        signature.send_keys(fellapp["usmlestep3"])

        # Open Education
        applicant_data_element = driver.find_element(By.CSS_SELECTOR,
                                                     "h4.panel-title > a[href='#education']")
        applicant_data_element.click()
        time.sleep(3)

        #med school
        self.automation.select_option(
            "s2id_oleg_fellappbundle_fellowshipapplication_trainings_2_institution", "CSS_SELECTOR",
            "#select2-drop .select2-input",
            fellapp["medschool"]
        )
        time.sleep(2)

        #residency_specialty s2id_oleg_fellappbundle_fellowshipapplication_trainings_3_residencySpecialty
        self.automation.select_option(
            "s2id_oleg_fellappbundle_fellowshipapplication_trainings_3_residencySpecialty", "CSS_SELECTOR",
            "#select2-drop .select2-input",
            fellapp["residency_specialty"][0]
        )
        time.sleep(2)

        #s2id_oleg_fellappbundle_fellowshipapplication_trainings_3_institution
        self.automation.select_option(
            "s2id_oleg_fellappbundle_fellowshipapplication_trainings_3_institution", "CSS_SELECTOR",
            "#select2-drop .select2-input",
            fellapp["residency_specialty"][1]
        )
        time.sleep(1)

        self.automation.select_option(
            "s2id_oleg_fellappbundle_fellowshipapplication_trainings_3_geoLocation_city", "CSS_SELECTOR",
            "#select2-drop .select2-input",
            fellapp["residency_specialty"][2]
        )
        time.sleep(1)

        self.automation.select_option(
            "s2id_oleg_fellappbundle_fellowshipapplication_trainings_3_geoLocation_state", "CSS_SELECTOR",
            "#select2-drop .select2-input",
            fellapp["residency_specialty"][3]
        )
        time.sleep(1)

        self.automation.select_option(
            "s2id_oleg_fellappbundle_fellowshipapplication_trainings_3_geoLocation_country", "CSS_SELECTOR",
            "#select2-drop .select2-input",
            fellapp["residency_specialty"][4]
        )
        time.sleep(1)

        #fellowship_specialty
        # self.automation.select_option(
        #     "s2id_oleg_fellappbundle_fellowshipapplication_trainings_4_majors", "CSS_SELECTOR",
        #     "#select2-drop .select2-input",
        #     fellapp["fellowship_specialty"][0]
        # )
        # time.sleep(1)
        #signature = driver.find_element(By.ID, "oleg_fellappbundle_fellowshipapplication_trainings_4_majors")
        #signature.send_keys(fellapp["fellowship_specialty"][0])

        ############ Post-Residency Fellowship Area of training ##############
        if 1:
            # wait = WebDriverWait(driver, 10)
            fellowship_major = fellapp["fellowship_specialty"][0]

            fellapp_major = driver.find_element(By.ID, "s2id_oleg_fellappbundle_fellowshipapplication_trainings_4_majors")
            time.sleep(1)
            fellapp_major.click()
            time.sleep(1)
            # child_input_div = fellapp_major.find_element(By.ID, "s2id_autogen3")
            fellapp_major_input_div = fellapp_major.find_element(By.CLASS_NAME, "select2-input")
            time.sleep(1)
            fellapp_major_input_div.send_keys(fellowship_major)
            time.sleep(1)
            fellapp_major_input_div.send_keys(Keys.ENTER)
            time.sleep(1)
        ############ EOF ##############

        self.automation.select_option(
            "s2id_oleg_fellappbundle_fellowshipapplication_trainings_4_institution", "CSS_SELECTOR",
            "#select2-drop .select2-input",
            fellapp["fellowship_specialty"][1]
        )
        time.sleep(1)

        self.automation.select_option(
            "s2id_oleg_fellappbundle_fellowshipapplication_trainings_4_geoLocation_city", "CSS_SELECTOR",
            "#select2-drop .select2-input",
            fellapp["fellowship_specialty"][2]
        )
        time.sleep(1)

        self.automation.select_option(
            "s2id_oleg_fellappbundle_fellowshipapplication_trainings_4_geoLocation_state", "CSS_SELECTOR",
            "#select2-drop .select2-input",
            fellapp["fellowship_specialty"][3]
        )
        time.sleep(1)

        self.automation.select_option(
            "s2id_oleg_fellappbundle_fellowshipapplication_trainings_4_geoLocation_country", "CSS_SELECTOR",
            "#select2-drop .select2-input",
            fellapp["fellowship_specialty"][4]
        )
        time.sleep(1)

        # Open Itinerary
        applicant_data_element = driver.find_element(By.CSS_SELECTOR, "h4.panel-title > a[href='#Itinerary']")
        applicant_data_element.click()
        time.sleep(3)
        #oleg_fellappbundle_fellowshipapplication_interviewDate interview_date '17/12/2026',
        interview_date_obj = datetime.datetime.strptime(fellapp["interview_date"], "%d/%m/%Y").date()
        # Format it back to 'd/m/Y' (this step is optional if you just need the date object)
        formatted_interview_date = interview_date_obj.strftime("%d/%m/%Y")

        ########################################################
        ################## Add interviewer #####################
        ########################################################
        applicant_data_element = driver.find_element(By.CSS_SELECTOR, "h4.panel-title > a[href='#interviews']")
        applicant_data_element.click()
        time.sleep(3)

        interviewer_count = 1 #interviews fields counter start with 1 when added
        interviewer_name = 'administrator'
        try:
            # driver, fellapp, formatted_interview_date, interviewer_name, count, with_rank=True
            self.set_interviewer(driver, fellapp, formatted_interview_date, interviewer_name, comments, interviewer_count)
            print(f"Success to add interviewer interviewer_name={interviewer_name}, count={interviewer_count}")
        except Exception as e:
            print(f"Failed to add interviewer interviewer_name={interviewer_name}, count={interviewer_count}:", e)

        interviewer_count = 2
        #interviewer_name = 'aeinstein'
        interviewer = users[random.randint(3, len(users) - 1)]
        interviewer_name = interviewer['displayName']
        try:
            # driver, fellapp, formatted_interview_date, interviewer_name, count, with_rank=True
            self.set_interviewer(driver, fellapp, formatted_interview_date, interviewer_name, comments, interviewer_count)
            print(f"Success to add interviewer interviewer_name={interviewer_name}, count={interviewer_count}")
        except Exception as e:
            print(f"Failed to add interviewer interviewer_name={interviewer_name}, count={interviewer_count}:", e)

        interviewer_count = 3
        # interviewer_name = 'aeinstein'
        interviewer = users[random.randint(3, len(users) - 1)]
        interviewer_name = interviewer['displayName']
        try:
            # driver, fellapp, formatted_interview_date, interviewer_name, count, with_rank=True
            self.set_interviewer(driver, fellapp, formatted_interview_date, interviewer_name, comments, interviewer_count, with_rank=False)
            print(f"Success to add interviewer interviewer_name={interviewer_name}, count={interviewer_count}")
        except Exception as e:
            print(f"Failed to add interviewer interviewer_name={interviewer_name}, count={interviewer_count}:", e)

        interview_date = driver.find_element(By.ID, "oleg_fellappbundle_fellowshipapplication_interviewDate")
        interview_date.clear()
        interview_date.send_keys(formatted_interview_date)
        time.sleep(5)

        ########################
        #### set references #####
        ########################
        applicant_data_element = driver.find_element(By.CSS_SELECTOR, "h4.panel-title > a[href='#recommendations']")
        applicant_data_element.click()
        time.sleep(3)
        self.set_reference(driver, references, count)
        self.set_reference(driver, references, count+1)
        self.set_reference(driver, references, count+2)
        ########################
        #### EOF set references #####
        ########################

        #click somewhere to close datepicker dialog box
        # body = driver.find_element(By.TAG_NAME, "body")
        # body.send_keys(Keys.ESCAPE)  # Close the datepicker
        # time.sleep(3)
        # driver.find_element(By.TAG_NAME, "body").click()
        # time.sleep(3)
        # signature_date = driver.find_element(By.ID, "oleg_fellappbundle_fellowshipapplication_signatureDate")
        # signature_date.click()  # Close the datepicker
        # time.sleep(3)
        signature_date_after = driver.find_element(By.ID, "oleg_fellappbundle_fellowshipapplication_signatureDate")
        driver.execute_script("arguments[0].scrollIntoView();", signature_date_after)
        # time.sleep(3)
        # driver.execute_script("document.querySelector('.datepicker-dropdown').style.display='none';")
        # driver.save_screenshot("fellapp_signature_date_after.png")
        #
        # button = driver.find_element(By.CLASS_NAME, "btn-warning")
        # driver.execute_script("arguments[0].scrollIntoView();", button)
        # driver.save_screenshot("create_single_fellapp_after_click_btn-warning.png")
        # time.sleep(1)

        driver.save_screenshot("create_single_fellapp_before_click_btn-warning.png")

        time.sleep(3)
        #click submit btn-warning
        #button = driver.find_element(By.CLASS_NAME, "btn-warning")
        button = driver.find_element(By.ID, "triggerSubmit")
        driver.execute_script("arguments[0].scrollIntoView();", button)
        driver.execute_script("arguments[0].click();", button)

        #driver.execute_script("arguments[0].scrollIntoView();", button)
        #driver.save_screenshot("create_single_fellapp_after_click_btn-warning.png")

        time.sleep(3)
        #Click 'Submit' button with id="submitSubmitBtn"
        #<button id="submitSubmitBtn" class="btn btn-primary">Submit</button>
        button = driver.find_element(By.ID, "submitSubmitBtn")
        time.sleep(1)
        button.click()
        print(f"Button to submit fellapp for {displayName} (submitSubmitBtn) clicked")
        time.sleep(3)

        #TODO: upload photo and itinerary by api
        if 1:
            current_url = driver.current_url
            print("Current URL:", current_url)
            # Extract the last part after the final slash
            fellapp_id = int(current_url.rstrip('/').split('/')[-1])
            print("Extracted fellapp ID:", fellapp_id)
            print("photo:", fellapp["photo"])

            resp = self.upload_fellowship_file(
                url=self.automation.baseurl.rstrip('/') + '/' + f"directory/api/upload-file/",
                fellapp_id=fellapp_id,
                file_name=fellapp["photo"],
                documenttype="Fellowship Photo",
                sitename="fellapp",
                headers = {
                    "Authorization": "Bearer 12832",
                    "Content-Type": "application/json"
                }
            )
            print("Fellowship Photo upload-file resp=",resp)
            #print(resp)  # should contain documentid and documentsrc

            resp = self.upload_fellowship_file(
                url=self.automation.baseurl.rstrip('/') + '/' + f"directory/api/upload-file/",
                fellapp_id=fellapp_id,
                file_name="sample_itinerary.pdf",
                documenttype="Itinerary",
                sitename="fellapp",
                headers={
                    "Authorization": "Bearer 12832",
                    "Content-Type": "application/json"
                }
            )
            print("Itinerary upload-file resp=", resp)
            #print(resp)  # should contain documentid and documentsrc

        #print("Finish new fellapp")
        time.sleep(5)

    def set_reference(self,driver,references,count):
        reference_first_name = references[count]['firstName']
        reference_last_name = references[count]['lastName']
        reference_display_name = references[count]['displayName']
        reference_email = references[count]['email']
        # reference_first_name
        try:
            first_name_input = WebDriverWait(driver, 10).until(
                EC.visibility_of_element_located(
                    (By.ID, f"oleg_fellappbundle_fellowshipapplication_references_{count}_firstName")
                )
            )
            # Clear any existing text and set new value
            first_name_input.clear()
            first_name_input.send_keys(reference_first_name)
            print(f"Success to add reference reference_first_name={reference_first_name}, count={count}")
        except Exception as e:
            print(f"Failed to add reference reference_first_name={reference_first_name}, count={count}:", e)
        # reference_last_name
        try:
            first_name_input = WebDriverWait(driver, 10).until(
                EC.visibility_of_element_located(
                    (By.ID, f"oleg_fellappbundle_fellowshipapplication_references_{count}_lastName")
                )
            )
            # Clear any existing text and set new value
            first_name_input.clear()
            first_name_input.send_keys(reference_last_name)
            print(f"Success to add reference reference_last_name={reference_last_name}, count={count}")
        except Exception as e:
            print(f"Failed to add reference reference_last_name={reference_last_name}, count={count}:", e)
        # reference_email
        try:
            email_input = WebDriverWait(driver, 10).until(
                EC.visibility_of_element_located(
                    (By.ID, f"oleg_fellappbundle_fellowshipapplication_references_{count}_email")
                )
            )
            # Clear any existing text and set new value
            email_input.clear()
            email_input.send_keys(reference_email)
            print(f"Success to add reference reference_email={reference_email}, count={count}")
        except Exception as e:
            print(f"Failed to add reference reference_email={reference_email}, count={count}:", e)

    #url - api/upload-file
    def upload_fellowship_file(self,
                               url: str,
                               fellapp_id: int,
                               file_name: str,
                               documenttype: str = "Fellowship Photo",
                               sitename: str = None,
                               headers: dict = None) -> dict:
        """
        Upload a file to the Symfony fellowship application API.

        Args:
            base_url (str): Base URL of your application (e.g. https://example.com).
            fellapp_id (int): Fellowship application ID.
            file_name (str): file name.
            documenttype (str, optional): Document type (default: Fellowship Photo).
            sitename (str, optional): Site name.
            headers (dict, optional): Extra headers (e.g. Authorization).

        Returns:
            dict: JSON response from the API.
        """
        print("upload fellowship_file url=", url)
        print("upload fellowship_file fellapp_id=", fellapp_id)
        print("upload fellowship_file file_name=", file_name)
        print("upload fellowship_file documenttype=", documenttype)
        print("upload fellowship_file sitename=", sitename)

        # Get the directory where the current script is located
        script_dir = os.path.dirname(os.path.abspath(__file__))
        # Build the relative path
        #relative_path = f"../../orderflex/src/App/FellAppBundle/Util/{file_name}"
        relative_path = f"src/App/FellAppBundle/Util/{file_name}"
        # Resolve to an absolute path
        file_path = os.path.abspath(os.path.join(script_dir, relative_path))
        print(f"file_path={file_path}")
        print(f"relative_path={relative_path}")

        #accept_url = base_url.rstrip('/') + '/' + f"api/upload-file".lstrip('/')

        data = {
            "fellappid": fellapp_id,
            "filepath": os.path.basename(file_path),
            "relativepath": relative_path,
            "filename": file_name,
            "documenttype": documenttype,
            "sitename": sitename, #'fellapp'
        }

        driver = self.automation.get_driver()
        session = requests.Session()
        for cookie in driver.get_cookies():
            session.cookies.set(cookie['name'], cookie['value'])

        # Pass through headers (e.g. Authorization) if provided
        if headers:
            #response = requests.post(url, data=data, headers=headers)
            #response = requests.get(url, data=data, headers=headers)
            response = session.post(url, data=data, headers=headers)
            #response = session.post(url, data=data)
        else:
            #response = requests.post(url, data=data)
            #response = requests.get(url, data=data)
            response = session.post(url, data=data)
        #response.raise_for_status()  # raise error if status != 200

        # Try to parse JSON; if it fails, print diagnostics and re-raise
        try:
            return response.json()
            #return response.text
        except ValueError:
            print("upload_fellowship_file: non-JSON response received")
            print("Status:", response.status_code)
            print("Headers:", response.headers)
            print("Body snippet:")
            print(response.text)
            raise

        # if not os.path.isfile(file_path):
        #     raise FileNotFoundError(f"File not found: {file_path}")
        #
        # with open(file_path, "rb") as f:
        #     files = {"file": f}
        #     data = {
        #         "fellapp_id": fellapp_id,
        #         "filepath": os.path.basename(file_path),
        #         "documenttype": documenttype,
        #     }
        #     if sitename:
        #         data["sitename"] = sitename
            # response = requests.post(url, files=files, data=data, headers=headers)
            # response.raise_for_status()  # raise error if status != 200
            # return response.json()

    def accept(self, fellapp_id):
        driver = self.automation.get_driver()
        accept_url = self.automation.baseurl.rstrip('/') + '/' + f"fellowship-applications/change-status/{fellapp_id}/accepted".lstrip('/')
        driver.get(accept_url)
        time.sleep(1)

    #user-itinerarys
    #user-photo
    def add_file(self,file_name):
        driver = self.automation.get_driver()

        # Get the directory where the current script is located
        script_dir = os.path.dirname(os.path.abspath(__file__))
        # Build the relative path
        relative_path = f"../../orderflex/src/App/FellAppBundle/Util/{file_name}"
        # Resolve to an absolute path
        file_path = os.path.abspath(os.path.join(script_dir, relative_path))
        print(f"file_path={file_path}")

        # Step 1: Click the dropzone container
        container = WebDriverWait(driver, 120).until(
            EC.presence_of_element_located(
                (By.CSS_SELECTOR, "div.well.form-element-holder.user-photo.user-FellowshipApplication")
            )
        )
        print("container found")
        time.sleep(3)
        # Step 2: Send file path to hidden input
        file_input = WebDriverWait(container, 120).until(
            lambda c: c.find_element(By.CSS_SELECTOR, "input[type='file']")
        )
        print("input file found")
        time.sleep(3)

        # Make the input visible if it's hidden (optional, but often necessary)
        driver.execute_script("arguments[0].style.display = 'block';", file_input)
        time.sleep(5)

        file_input.send_keys(file_path)

        #file_input = driver.find_element("css selector", "input.dz-hidden-input")
        # Send the absolute path of the file you want to upload
        file_input.send_keys(file_path)

        try:
            # Wait briefly for alert to appear
            time.sleep(3)
            alert = driver.switch_to.alert
            print(f"Alert text: {alert.text}")
            alert.accept()  # Click OK
            print("Alert accepted.")
        except NoAlertPresentException:
            print("No alert appeared after file upload.")

    def set_interviewer(self, driver, fellapp, formatted_interview_date, interviewer_name, comments, count, with_rank=True):
        # Wait until the button is present and clickable
        add_button = WebDriverWait(driver, 10).until(
            EC.element_to_be_clickable((By.XPATH, "//button[contains(text(), 'Add Interviewer')]"))
        )
        add_button.click()
        print(f"Clicked 'Add Interviewer' button. To add interviewer {interviewer_name}, count={count}")

        time.sleep(5)
        print(f"Check if comment field exists for {interviewer_name}")
        try:
            WebDriverWait(driver, 5).until(
                EC.presence_of_element_located((By.ID, f"oleg_fellappbundle_fellowshipapplication_interviews_{count}_comment"))
            )
            print(f"Comment field exists for {interviewer_name}")
        except TimeoutException:
            print(f"Comment field not found for {interviewer_name}")

        # s2id_oleg_fellappbundle_fellowshipapplication_interviews_1_interviewer add administrator
        self.automation.select_option(
            f"s2id_oleg_fellappbundle_fellowshipapplication_interviews_{count}_interviewer", "CSS_SELECTOR",
            "#select2-drop .select2-input",
            f"{interviewer_name}"
        )
        time.sleep(1)

        # oleg_fellappbundle_fellowshipapplication_interviews_1_totalRank
        # signature = driver.find_element(By.ID, "oleg_fellappbundle_fellowshipapplication_interviews_1_totalRank")
        # signature.send_keys(fellapp["interview_score"])

        if with_rank:
            total_rank = 0

            # s2id_oleg_fellappbundle_fellowshipapplication_interviews_0_academicRank
            try:
                time.sleep(1)
                # Choose a random academic rank from 1 to 5
                academic_rank_value = random.randint(1, 5)
                print(f"Setting academicRank to: {academic_rank_value}")
                # Inject the value into the Select2-enhanced field
                script = f"""
                    $("#oleg_fellappbundle_fellowshipapplication_interviews_{count}_academicRank")
                        .val("{academic_rank_value}")
                        .trigger("change");
                """
                driver.execute_script(script)
                time.sleep(1)
                total_rank = total_rank + academic_rank_value
                print(f"✓ academicRank set to {academic_rank_value} for {interviewer_name}, count={count}")
            except Exception as e:
                print(f"Error setting academicRank for {interviewer_name}, count={count}: {e}")

            # oleg_fellappbundle_fellowshipapplication_interviews_0_personalityRank
            try:
                time.sleep(1)
                # Choose a random rank from 1 to 5
                academic_rank_value = random.randint(1, 5)
                print(f"Setting personalityRank to: {academic_rank_value}")
                # Inject the value into the Select2-enhanced field
                script = f"""
                    $("#oleg_fellappbundle_fellowshipapplication_interviews_{count}_personalityRank")
                        .val("{academic_rank_value}")
                        .trigger("change");
                """
                driver.execute_script(script)
                time.sleep(1)
                total_rank = total_rank + academic_rank_value
                print(f"✓ personalityRank set to {academic_rank_value} for {interviewer_name}, count={count}")
            except Exception as e:
                print(f"Error setting personalityRank for {interviewer_name}, count={count}: {e}")

            # oleg_fellappbundle_fellowshipapplication_interviews_0_potentialRank
            try:
                time.sleep(1)
                # Choose a random rank from 1 to 5
                academic_rank_value = random.randint(1, 5)
                print(f"Setting potentialRank to: {academic_rank_value}")
                # Inject the value into the Select2-enhanced field
                script = f"""
                            $("#oleg_fellappbundle_fellowshipapplication_interviews_{count}_potentialRank")
                                .val("{academic_rank_value}")
                                .trigger("change");
                        """
                driver.execute_script(script)
                time.sleep(1)
                total_rank = total_rank + academic_rank_value
                print(f"✓ potentialRank set to {academic_rank_value} for {interviewer_name}, count={count}")
            except Exception as e:
                print(f"Error setting potentialRank for {interviewer_name}, count={count}: {e}")

            try:
                # Set total rank total_rank oleg_fellappbundle_fellowshipapplication_interviews_0_totalRank
                time.sleep(1)
                total_rank = round(total_rank / 3, 1)
                print(f"Before set total_rankt: {total_rank}")
                comment_field = WebDriverWait(driver, 10).until(
                    EC.visibility_of_element_located(
                        (By.ID, f"oleg_fellappbundle_fellowshipapplication_interviews_{count}_totalRank"))
                )
                comment_field.clear()
                comment_field.send_keys(total_rank)
                time.sleep(1)
                print(f"✓ total_rank set: {total_rank}")
            except Exception as e:
                print(f"Error setting totalRank for {interviewer_name}, count={count}: {e}")

            # oleg_fellappbundle_fellowshipapplication_interviews_0_comment
            time.sleep(1)
            #comment_text = fellapp['comment']
            print(f"Before set rank comment text",comments)
            comment_text = random.choice(comments)
            print(f"Set rank comment text: {comment_text}")
            comment_field = WebDriverWait(driver, 10).until(
                EC.visibility_of_element_located(
                    (By.ID, f"oleg_fellappbundle_fellowshipapplication_interviews_{count}_comment"))
            )
            comment_field.clear()
            comment_field.send_keys(comment_text)
            time.sleep(1)
            print(f"✓ Interview comment set: {comment_text}")

        #select language
        try:
            time.sleep(1)
            # Choose a random rank from 1 to 3
            language_value = random.randint(1, 2)
            print(f"Setting language to: {language_value}")
            # Inject the value into the Select2-enhanced field
            script = f"""
                        $("#oleg_fellappbundle_fellowshipapplication_interviews_{count}_languageProficiency")
                            .val("{language_value}")
                            .trigger("change");
                    """
            driver.execute_script(script)
            time.sleep(1)
            print(f"✓ language set to {language_value} for {interviewer_name}, count={count}")
        except Exception as e:
            print(f"Error setting language for {interviewer_name}, count={count}: {e}")

        time.sleep(1)
        # interview_date
        # oleg_fellappbundle_fellowshipapplication_interviewDate interview_date '17/12/2026',
        interview_date = driver.find_element(By.ID,
                                             f"oleg_fellappbundle_fellowshipapplication_interviews_{count}_interviewDate")
        interview_date.clear()
        interview_date.send_keys(formatted_interview_date)
        time.sleep(3)

def main():
    url = None
    username_text = "administrator"
    password_text = "1234567890"

    # url = "http://127.0.0.1/directory/"
    # username_text = "oli2002l"
    # password_text = "pass"

    run_by_symfony_command = False
    baseurl = "https://view.online/c/demo-institution/demo-department"

    #baseurl = "http://127.0.0.1"
    #username_text = "oli2002l"
    #password_text = "pass"

    # First, process the fellowship configurations in small batches
    automation = WebAutomation(baseurl, run_by_symfony_command)
    automation.login_to_site()
    fellapp = FellApp(automation)

    ######## Test the file upload ########
    if 0:
        automation.login_to_site()
        fellapp_id = 1
        url = automation.baseurl.rstrip('/') + '/' + f"directory/api/upload-file/"
        print(f"upload-file url={url}")
        resp = fellapp.upload_fellowship_file(
            #url=automation.baseurl.rstrip('/') + '/' + f"api/upload-file",
            url=url,
            fellapp_id=fellapp_id,
            file_name="lisa-chen.jpeg",
            documenttype="Fellowship Photo",
            sitename="fellapp",
            headers={
                "Authorization": "Bearer 12832",
                "Content-Type": "application/json"
            }
        )
        print("upload fellowship_file response=", resp)

        del fellapp
        automation.quit_driver()
        del automation

        exit()
    ######## EOF Test the file upload #######

    # Process in batches of 3
    if 0:
        fellapp.configs(max_count=1, batch_size=3)

    # Set site settings after all configurations are done
    if 0:
        fellapp.set_site_settings()

    # Clean up
    automation.quit_driver()
    del fellapp
    del automation

    # Now process the fellowship applications
    if 1:
        automation = WebAutomation(baseurl, run_by_symfony_command)
        automation.login_to_site()
        fellapp = FellApp(automation)

        # Create fellowship applications
        fellapp.create_fellapps(max_count=1)
        time.sleep(3)

        # Accept applications
        fellapp.accept(1)
        time.sleep(3)

    print("FellApp done!")

    #automation.quit_driver()
    if 'fellapp' in locals():
        del fellapp
    if 'automation' in locals():
        automation.quit_driver()
        del automation

if __name__ == "__main__":
    main()