from web_automation import WebAutomation
from users import Users
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import Select
from selenium.webdriver.support import expected_conditions as EC
#from selenium.webdriver.common.action_chains import ActionChains
import time
import datetime
#from datetime import date
#from dateutil.relativedelta import relativedelta
#from selenium.webdriver.support.expected_conditions import visibility_of_all_elements_located
from selenium.common.exceptions import NoSuchElementException



class FellApp:
    def __init__(self, automation):
        self.automation = automation
        self.users = Users(automation)
        self.existing_users = self.users.get_existing_users(with_admin=True)

    def get_fell_apps(self):
        fellapps = []
        fellapps.append({
            'type': '1',  # 'Clinical Informatics'
            'firstName': 'Joe',
            'lastName': 'Simpson',
            'displayName': 'Joe Simpson',
            'email': 'cinava@yahoo.com',
            'usmlestep1': 'Pass',
            'usmlestep2': 253,
            'usmlestep3': 242,
            'medschool': 'Johns Hopkins University School of Medicine',
            #AP/CP Pathology — Massachusetts General Hospital / Harvard Medical School
            #AP/CP, school, year (current + 1), city, state, country
            'residency_specialty': ['AP/CP', 'Massachusetts General Hospital / Harvard Medical School', 'Boston', 'Massachusetts', 'United States'],
            #Surgical Pathology Fellowship — Memorial Sloan Kettering Cancer Center
            #specialty, school, year (current + 2), city, state, country
            'fellowship_specialty': ['Surgical Pathology Fellowship', 'Memorial Sloan Kettering Cancer Center', 'New-York', 'New-York', 'United States'],
            'interview_date': '09/12/2026',
            'interview_score': '4.3'
        })
        fellapps.append({
            'type': '1',  # 'Clinical Informatics'
            'firstName': 'Soleil',
            'lastName': 'Teresia',
            'displayName': 'Soleil Teresia',
            'email': 'cinava@yahoo.com',
            'usmlestep1': 'Pass',
            'usmlestep2': 247,
            'usmlestep3': 238,
            'medschool': 'Washington University School of Medicine in St. Louis',
            'residency_specialty': ['AP', 'University of California', 'San Francisco', 'California', 'United States'],
            'fellowship_specialty': ['Breast Pathology Fellowship', 'Mayo Clinic', 'Rochester', 'New-York', 'United States'],
            'interview_date': '14/12/2026',
            'interview_score': '3.9'
        })
        fellapps.append({
            'type': '1',  # 'Clinical Informatics'
            'firstName': 'Haides',
            'lastName': 'Neon',
            'displayName': 'Haides Neon',
            'email': 'cinava@yahoo.com',
            'usmlestep1': 'Pass',
            'usmlestep2': 258,
            'usmlestep3': 244,
            'medschool': 'University of Pennsylvania Perelman School of Medicine',
            'residency_specialty': ['CP', 'Stanford University Medical Center', 'Stanford', 'California', 'United States'],
            'fellowship_specialty': ['Hematopathology Fellowship', 'MD Anderson Cancer Center', 'Houston', 'Texas', 'United States'],
            'interview_date': '17/12/2026',
            'interview_score': '4.2'
        })

        return fellapps

    def configs(self):
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

        users = self.users.get_users()

        for fellapp_name in fellapp_names:
            time.sleep(3)
            #self.config_single(fellapp_name)
            self.config_single_more(fellapp_name, users)
            #break #testing

    def config_single(self, fellapp_name):
        driver = self.automation.get_driver()
        #Add Fellowship Subspecialty: https://view.online/c/demo-institution/demo-department/directory/admin/list-manager/id/1/37
        #url = "https://view.online/c/demo-institution/demo-department/directory/admin/list-manager/?filter%5Bsearch%5D=Subspecialty&filter%5Btype%5D%5B%5D=default&filter%5Btype%5D%5B%5D=user-added"
        fellapp_type_url = self.automation.baseurl.rstrip('/') + '/' + "directory/admin/list/edit-by-listname/FellowshipSubspecialty".lstrip('/')
        driver.get(fellapp_type_url)

        time.sleep(1)

        # Wait for the table to load
        wait = WebDriverWait(driver, 10)  # Adjust timeout as needed
        table = wait.until(EC.presence_of_element_located((By.CLASS_NAME, 'records_list')))

        # Locate the <td> with the exact text "Clinical Informatics"
        try:
            #target_td = table.find_element(By.XPATH, './/td[text()="Clinical Informatics"]')
            target_td = table.find_element(By.XPATH, f'.//td[text()="{fellapp_name}"]')
            if target_td:
                #print("<td> with text 'Clinical Informatics' already exists.")
                #print("Class name of the <td> is:", target_td.get_attribute('class'))  # Print the class name of the <td>
                pass
            else:
                #create
                create_link = table.find_element(By.XPATH, './/a[text()="Create a new entry"]')
                create_link.click()
                time.sleep(3)

                name = driver.find_element(By.ID, "oleg_userdirectorybundle_genericlist_list_name")
                #name.send_keys("Clinical Informatics")
                name.send_keys(fellapp_name)

                #In new version, institution is not required for FellowshipSubspecialty
                # #s2id_oleg_userdirectorybundle_genericlist_institution
                # self.automation.select_option("s2id_oleg_userdirectorybundle_genericlist_institution", "CSS_SELECTOR",
                #                               ".select2-search .select2-input",
                #                               "Pathology and Laboratory Medicine"
                #                               )
                time.sleep(3)

                self.automation.click_button_by_id("oleg_userdirectorybundle_genericlist_submit")

        except:
            print(f"fellapp configs: Unable to find or create {fellapp_name}")

        time.sleep(3)

        #Create fellowship type
        fellowship_type_url = self.automation.baseurl.rstrip('/') + '/' + "fellowship-applications/fellowship-types-settings".lstrip('/')
        driver.get(fellowship_type_url)

        time.sleep(3)

        #<a href="/c/demo-institution/demo-department/fellowship-applications/fellowship-type/edit/1">Clinical Informatics</a>
        #fellowship_type = table.find_element(By.XPATH, './/a[text()="Clinical Informatics"]')
        #fellowship_type = driver.find_element("xpath", "//h4/a[contains(text(), 'Clinical Informatics')]")
        # fellowship_type = WebDriverWait(driver, 10).until(
        #     EC.presence_of_element_located((By.XPATH, "//h4/a[contains(text(), 'Clinical Informatics')]"))
        # )
        try:
            # Try to find the element
            #fellowship_type = driver.find_element("xpath", "//h4/a[contains(text(), 'Clinical Informatics')]")
            fellowship_type = driver.find_element("xpath", f"//h4/a[contains(text(), '{fellapp_name}')]")
            #print("Element found!")
            # You can perform actions on the element here
            fellowship_type.click()
            time.sleep(3)

            # #Add coordinator
            # users = self.users.get_users()
            #
            # # add coordinator
            # coordinator = users[2]
            # print(f"configs: coordinator: {coordinator['displayName']}")
            #
            # # s2id_oleg_fellappbundle_fellowshipSubspecialty_coordinators
            # self.automation.select_option("s2id_oleg_fellappbundle_fellowshipSubspecialty_coordinators", "CSS_SELECTOR",
            #                               ".select2-choices .select2-input",
            #                               coordinator["displayName"]
            #                               )
            #
            # time.sleep(3)
            #
            # driver.execute_script("document.getElementById('select2-drop-mask').style.display = 'none';")
            # time.sleep(3)
            #
            # # click Update button btn btn-warning
            # self.automation.click_button("btn-warning")
            # button = driver.find_element(By.CLASS_NAME, "btn-warning")
            # driver.execute_script("arguments[0].scrollIntoView();", button)
            # driver.save_screenshot("configs_after_click_btn-warning.png")
        except NoSuchElementException:
            # create new fellowship type "Clinical Informatics"
            #print("create new fellowship type Clinical Informatics")
            self.automation.click_button("btn-primary")
            time.sleep(3)

            # self.automation.select_option(
            #     "s2id_oleg_fellappbundle_fellappfellowshipapplicationtype_fellowshipsubspecialtytype", "CSS_SELECTOR",
            #     ".select2-search .select2-input",
            #     "Clinical Informatics"
            #     )
            self.automation.select_option(
                "s2id_oleg_fellappbundle_fellappfellowshipapplicationtype_fellowshipsubspecialtytype", "CSS_SELECTOR",
                ".select2-search .select2-input",
                fellapp_name
            )

            time.sleep(3)
            self.automation.click_button_by_id("oleg_fellappbundle_fellappfellowshipapplicationtype_save")

        time.sleep(3)

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

            # add coordinator with explicit waits
            try:
                coordinator = users[2]
                # user_id = self.users.get_existing_user('John Doe')
                user_id = self.existing_users[coordinator['displayName']]
                print(f"coordinator {coordinator['displayName']} User ID: {user_id}")
                script = f"""
                            $("#s2id_oleg_fellappbundle_fellowshipSubspecialty_coordinators").select2('val','{user_id}');
                        """
                driver.execute_script(script)
                time.sleep(1)
                #driver.find_element(By.TAG_NAME, "body").click()
                #time.sleep(1)

                # print(f"config_single_more: {fellapp_name} coordinator: {coordinator['displayName']}")
                # combobox = wait.until(
                #     EC.element_to_be_clickable((By.ID, "s2id_oleg_fellappbundle_fellowshipSubspecialty_coordinators"))
                # )
                #
                # # Check if already selected
                # selected_labels = combobox.find_elements(By.CSS_SELECTOR, ".select2-search-choice div")
                # already_selected = any(coordinator["displayName"] in label.text for label in selected_labels)
                #
                # if not already_selected:
                #     combobox.click()
                #     search_box = wait.until(
                #         EC.presence_of_element_located((By.CSS_SELECTOR, ".select2-choices .select2-input"))
                #     )
                #     search_box.send_keys(coordinator["displayName"])
                #     search_box.send_keys(Keys.ENTER)
                #     time.sleep(1)
                #
                # # Always clean up the mask and dropdown
                # wait.until(EC.invisibility_of_element_located((By.ID, "select2-drop-mask")))
                # driver.find_element(By.TAG_NAME, "body").click()
                # time.sleep(1)
                print(f"config_single_more: {fellapp_name} coordinator added: {coordinator['displayName']}")
            except Exception as e:
                print(f"config_single_more: unable to set coordinator {coordinator['displayName']} for {fellapp_name}: {e}")

            #print("testing exit")
            #exit()

            # add director with explicit waits
            try:
                director = users[3]
                print(f"config_single_more: {fellapp_name} director: {director['displayName']}")
                user_id = self.existing_users[director['displayName']]
                print(f"director {director['displayName']} User ID: {user_id}")
                script = f"""
                    $("#s2id_oleg_fellappbundle_fellowshipSubspecialty_directors").select2('val','{user_id}');
                """
                driver.execute_script(script)
                time.sleep(1)
                #driver.find_element(By.TAG_NAME, "body").click()
                #time.sleep(1)

                # combobox = wait.until(
                #     EC.element_to_be_clickable((By.ID, "s2id_oleg_fellappbundle_fellowshipSubspecialty_directors"))
                # )
                #
                # # Check if already selected
                # selected_labels = combobox.find_elements(By.CSS_SELECTOR, ".select2-search-choice div")
                # already_selected = any(director["displayName"] in label.text for label in selected_labels)
                #
                # if not already_selected:
                #     combobox.click()
                #     search_box = wait.until(
                #         EC.presence_of_element_located((By.CSS_SELECTOR, ".select2-choices .select2-input"))
                #     )
                #     search_box.send_keys(director["displayName"])
                #     search_box.send_keys(Keys.ENTER)
                #     time.sleep(1)
                #     driver.execute_script("document.getElementById('select2-drop-mask').style.display = 'none';")
                #     time.sleep(1)
                # driver.find_element(By.TAG_NAME, "body").click()
                # time.sleep(1)
                print(f"config_single_more: {fellapp_name} director added: {director['displayName']}")
            except Exception as e:
                print(f"config_single_more: unable to set director {director['displayName']} for {fellapp_name}: {e}")

            # add interviewer with explicit waits
            try:
                interviewer = "Admin Admin"
                print(f"config_single_more: {fellapp_name}, add interviewer: {interviewer}")
                print("self.existing_users:",self.existing_users)
                user_id = self.existing_users[interviewer]
                #user_id = 2 #id of administrator #self.existing_users['administrator']
                print(f"interviewer ({interviewer}) user ID: {user_id}")
                script = f"""
                    $("#s2id_oleg_fellappbundle_fellowshipSubspecialty_interviewers").select2('val','{user_id}');
                """
                driver.execute_script(script)
                time.sleep(1)
                #driver.find_element(By.TAG_NAME, "body").click()
                #time.sleep(1)

                # combobox = wait.until(
                #     EC.element_to_be_clickable((By.ID, "s2id_oleg_fellappbundle_fellowshipSubspecialty_interviewers"))
                # )
                #
                # # Check if already selected
                # selected_labels = combobox.find_elements(By.CSS_SELECTOR, ".select2-search-choice div")
                # already_selected = any(interviewer in label.text for label in selected_labels)
                #
                # if not already_selected:
                #     combobox.click()
                #     search_box = wait.until(
                #         EC.presence_of_element_located((By.CSS_SELECTOR, ".select2-choices .select2-input"))
                #     )
                #     search_box.send_keys(interviewer)
                #     search_box.send_keys(Keys.ENTER)
                #     time.sleep(1)
                #     driver.execute_script("document.getElementById('select2-drop-mask').style.display = 'none';")
                #     time.sleep(1)
                # driver.find_element(By.TAG_NAME, "body").click()
                # time.sleep(1)
                print(f"config_single_more: {fellapp_name} interviewer added: {interviewer}")
            except Exception as e:
                print(f"config_single_more: unable to set interviewer {interviewer} for {fellapp_name}: {e}")
            # try:
            #     # print(f"config_single_more: {fellapp_name} interviewer: administrator")
            #     # combobox = wait.until(
            #     #     EC.element_to_be_clickable((By.ID, "s2id_oleg_fellappbundle_fellowshipSubspecialty_interviewers"))
            #     # )
            #     # combobox.click()
            #     # search_box = wait.until(
            #     #     EC.presence_of_element_located((By.CSS_SELECTOR, ".select2-choices .select2-input"))
            #     # )
            #     # search_box.send_keys("administrator")
            #     # search_box.send_keys(Keys.ENTER)
            #     # time.sleep(1)
            #     # driver.execute_script("document.getElementById('select2-drop-mask').style.display = 'none';")
            #
            #     # Step 1: Locate the specific Select2 container
            #     container = wait.until(EC.presence_of_element_located(
            #         (By.ID, "s2id_oleg_fellappbundle_fellowshipSubspecialty_interviewers")
            #     ))
            #
            #     # Step 2: Find the input inside that container
            #     input_box = container.find_element(By.CSS_SELECTOR, "input.select2-input")
            #     print(f"config_single_more: finded input.select2-input")
            #     time.sleep(3)
            #
            #     # Step 3: Interact with the input
            #     input_box.click()
            #     input_box.clear()
            #     time.sleep(3)
            #     print(f"config_single_more: clicked input field")
            #
            #     input_box.send_keys("administrator")
            #     time.sleep(3)
            #     print(f"config_single_more: typed administrator")
            #
            #     # Step 4: Wait for dropdown and select the correct option
            #     option = wait.until(EC.element_to_be_clickable((
            #         By.XPATH,
            #         "//div[contains(@class, 'select2-result-label') and contains(text(), 'administrator')]"
            #     )))
            #     option.click()
            #     time.sleep(3)
            #     print(f"config_single_more: after click")
            #
            #     driver.find_element(By.TAG_NAME, "body").click()
            #     time.sleep(3)
            #
            #     #Step 5: Wait for the Select2 mask to disappear
            #     wait.until(EC.invisibility_of_element_located((By.ID, "select2-drop-mask")))
            #
            #     time.sleep(1)
            #     print(f"config_single_more: {fellapp_name} director added: administrator")
            # except Exception as e:
            #     print(f"config_single_more: unable to set interviewer administrator for {fellapp_name}: {e}")

            button = driver.find_element(By.CLASS_NAME, "btn-warning")
            driver.execute_script("arguments[0].scrollIntoView();", button)
            driver.save_screenshot("configs_after_click_btn-warning.png")

            # click Update button btn btn-warning
            time.sleep(3)
            self.automation.click_button("btn-warning")
            print(f"config_single_more: after click Update button for {fellapp_name}")

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

    def create_fellapps(self):
        for fellapp in self.get_fell_apps():
            self.create_single_fellapp(fellapp)
            #break #enable for test run only one

    def create_single_fellapp(self, fellapp):
        driver = self.automation.get_driver()
        url = self.automation.baseurl.rstrip('/') + '/' + "fellowship-applications/new/".lstrip('/')
        #url = "http://127.0.0.1/fellowship-applications/new/"
        driver.get(url)

        time.sleep(1)

        #Create a new fellapp https://view.online/c/demo-institution/demo-department/fellowship-applications/new/
        #print("create new fellowship application")

        applicant_data_element = driver.find_element(By.CSS_SELECTOR,
                                                     "h4.panel-title > a[href='#fellowshipApplicantData']")
        applicant_data_element.click()
        time.sleep(3)

        self.automation.select_option(
            "s2id_oleg_fellappbundle_fellowshipapplication_fellowshipSubspecialty", "CSS_SELECTOR",
            "#select2-drop .select2-input",
            "Clinical Informatics"
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
        time.sleep(1)

        #residency_specialty s2id_oleg_fellappbundle_fellowshipapplication_trainings_3_residencySpecialty
        self.automation.select_option(
            "s2id_oleg_fellappbundle_fellowshipapplication_trainings_3_residencySpecialty", "CSS_SELECTOR",
            "#select2-drop .select2-input",
            fellapp["residency_specialty"][0]
        )
        time.sleep(1)

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

        #Add interviewer administrator
        applicant_data_element = driver.find_element(By.CSS_SELECTOR, "h4.panel-title > a[href='#interviews']")
        applicant_data_element.click()
        time.sleep(3)

        try:
            # Wait until the button is present and clickable
            add_button = WebDriverWait(driver, 10).until(
                EC.element_to_be_clickable((By.XPATH, "//button[contains(text(), 'Add Interviewer')]"))
            )
            add_button.click()
            time.sleep(2)
            print("Clicked 'Add Interviewer' button.")

            #s2id_oleg_fellappbundle_fellowshipapplication_interviews_1_interviewer add administrator
            self.automation.select_option(
                "s2id_oleg_fellappbundle_fellowshipapplication_interviews_1_interviewer", "CSS_SELECTOR",
                "#select2-drop .select2-input",
                'administrator'
            )

            #oleg_fellappbundle_fellowshipapplication_interviews_1_totalRank
            signature = driver.find_element(By.ID, "oleg_fellappbundle_fellowshipapplication_interviews_1_totalRank")
            signature.send_keys(fellapp["interview_score"])

            #interview_date
            # oleg_fellappbundle_fellowshipapplication_interviewDate interview_date '17/12/2026',

            interview_date = driver.find_element(By.ID, "oleg_fellappbundle_fellowshipapplication_interviews_1_interviewDate")
            interview_date.clear()
            interview_date.send_keys(formatted_interview_date)
            time.sleep(3)

        except Exception as e:
            print("Failed to click the button:", e)

        interview_date = driver.find_element(By.ID, "oleg_fellappbundle_fellowshipapplication_interviewDate")
        interview_date.clear()
        interview_date.send_keys(formatted_interview_date)
        time.sleep(5)

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
        button.click()

        #print("Finish new fellapp")
        time.sleep(10)

    def accept(self, fellapp_id):
        driver = self.automation.get_driver()
        accept_url = self.automation.baseurl.rstrip('/') + '/' + f"fellowship-applications/change-status/{fellapp_id}/accepted".lstrip('/')
        driver.get(accept_url)
        time.sleep(1)


def main():
    url = None
    username_text = "administrator"
    password_text = "1234567890"

    # url = "http://127.0.0.1/directory/"
    # username_text = "oli2002l"
    # password_text = "pass"

    baseurl = "https://view.online/c/demo-institution/demo-department"
    automation = WebAutomation(baseurl, False)
    automation.login_to_site(url, username_text, password_text)

    fellapp = FellApp(automation)
    fellapp.configs()
    #fellapp.set_site_settings()
    #fellapp.create_fellapps()
    #fellapp.accept(1)

    print("FellApp done!")

    automation.quit_driver()

if __name__ == "__main__":
    main()