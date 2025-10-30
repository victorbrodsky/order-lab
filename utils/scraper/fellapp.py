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
        self.existing_users = self.users.get_existing_users()

    def get_fell_apps(self):
        users = []
        users.append({
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
        users.append({
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
        users.append({
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

        return users

    def configs(self):
        fellapp_names = {
            'Clinical Informatics',
             #'Breast Pathology',
             #'Cytopathology',
            # 'Dermatopathology',
            # 'Genitourinary Pathology',
            # 'Gynecologic Pathology',
            # 'Hematopathology',
            # 'Renal Pathology',
            # 'Surgical Pathology',
            # 'Molecular Genetic Pathology',
        }

        for fellapp_name in fellapp_names:
            time.sleep(3)
            self.config_single(fellapp_name)

    def config_single(self, fellapp_name):
        driver = self.automation.get_driver()
        #Add Fellowship Subspecialty: https://view.online/c/demo-institution/demo-department/directory/admin/list-manager/id/1/37
        #url = "https://view.online/c/demo-institution/demo-department/directory/admin/list-manager/?filter%5Bsearch%5D=Subspecialty&filter%5Btype%5D%5B%5D=default&filter%5Btype%5D%5B%5D=user-added"
        fellapp_type_url = "https://view.online/c/demo-institution/demo-department/directory/admin/list/edit-by-listname/FellowshipSubspecialty"
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

                #s2id_oleg_userdirectorybundle_genericlist_institution
                self.automation.select_option("s2id_oleg_userdirectorybundle_genericlist_institution", "CSS_SELECTOR",
                                              ".select2-search .select2-input",
                                              "Pathology and Laboratory Medicine"
                                              )
                time.sleep(3)

                self.automation.click_button_by_id("oleg_userdirectorybundle_genericlist_submit")

        except:
            print(f"fellapp configs: Unable to find or create {fellapp_name}")

        time.sleep(3)

        #Create fellowship type
        fellowship_type_url = "https://view.online/c/demo-institution/demo-department/fellowship-applications/fellowship-types-settings"
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

            users = self.users.get_users()

            # add coordinator
            coordinator = users[2]
            print(f"configs: coordinator: {coordinator['displayName']}")

            # s2id_oleg_fellappbundle_fellowshipSubspecialty_coordinators
            self.automation.select_option("s2id_oleg_fellappbundle_fellowshipSubspecialty_coordinators", "CSS_SELECTOR",
                                          ".select2-choices .select2-input",
                                          coordinator["displayName"]
                                          )

            time.sleep(3)

            driver.execute_script("document.getElementById('select2-drop-mask').style.display = 'none';")
            time.sleep(3)

            # click Update button btn btn-warning
            self.automation.click_button("btn-warning")
            button = driver.find_element(By.CLASS_NAME, "btn-warning")
            driver.execute_script("arguments[0].scrollIntoView();", button)
            driver.save_screenshot("configs_after_click_btn-warning.png")
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

    def set_site_settings(self):
        # Set fellowship start/end dates
        # https://view.online/c/demo-institution/demo-department/fellowship-applications/settings/specific-site-parameters/edit-page/
        driver = self.automation.get_driver()
        url = "https://view.online/c/demo-institution/demo-department/fellowship-applications/settings/specific-site-parameters/edit-page/"
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

        self.automation.click_button_by_id("oleg_fellappbundle_fellappsiteparameter_save")
        time.sleep(3)
        print("fellappAcademicYear Start/End dates populated")

    def create_fellapps(self):
        for fellapp in self.get_fell_apps():
            self.create_single_fellapp(fellapp)
            break

    def create_single_fellapp(self, fellapp):
        driver = self.automation.get_driver()
        url = "https://view.online/c/demo-institution/demo-department/fellowship-applications/new/"
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

        #USMLE Step 1
        signature = driver.find_element(By.ID, "oleg_fellappbundle_fellowshipapplication_examinations_0_USMLEStep1Score")
        signature.send_keys(fellapp["usmlestep1"])
        time.sleep(1)

        signature = driver.find_element(By.ID, "oleg_fellappbundle_fellowshipapplication_examinations_0_USMLEStep2CKScore")
        signature.send_keys(fellapp["usmlestep2"])

        signature = driver.find_element(By.ID,
                                        "oleg_fellappbundle_fellowshipapplication_examinations_0_USMLEStep3Score")
        signature.send_keys(fellapp["usmlestep3"])

        #med school
        self.automation.select_option(
            "s2id_oleg_fellappbundle_fellowshipapplication_trainings_0_institution", "CSS_SELECTOR",
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
            "oleg_fellappbundle_fellowshipapplication_trainings_3_geoLocation_city", "CSS_SELECTOR",
            "#select2-drop .select2-input",
            fellapp["residency_specialty"][2]
        )
        time.sleep(1)

        self.automation.select_option(
            "oleg_fellappbundle_fellowshipapplication_trainings_3_geoLocation_state", "CSS_SELECTOR",
            "#select2-drop .select2-input",
            fellapp["residency_specialty"][3]
        )
        time.sleep(1)

        self.automation.select_option(
            "oleg_fellappbundle_fellowshipapplication_trainings_3_geoLocation_country", "CSS_SELECTOR",
            "#select2-drop .select2-input",
            fellapp["residency_specialty"][4]
        )
        time.sleep(1)

        #fellowship_specialty
        self.automation.select_option(
            "oleg_fellappbundle_fellowshipapplication_trainings_4_majors", "CSS_SELECTOR",
            "#select2-drop .select2-input",
            fellapp["fellowship_specialty"][0]
        )
        time.sleep(1)

        self.automation.select_option(
            "s2id_oleg_fellappbundle_fellowshipapplication_trainings_4_institution", "CSS_SELECTOR",
            "#select2-drop .select2-input",
            fellapp["fellowship_specialty"][1]
        )
        time.sleep(1)

        self.automation.select_option(
            "oleg_fellappbundle_fellowshipapplication_trainings_4_geoLocation_city", "CSS_SELECTOR",
            "#select2-drop .select2-input",
            fellapp["fellowship_specialty"][2]
        )
        time.sleep(1)

        self.automation.select_option(
            "oleg_fellappbundle_fellowshipapplication_trainings_4_geoLocation_state", "CSS_SELECTOR",
            "#select2-drop .select2-input",
            fellapp["fellowship_specialty"][3]
        )
        time.sleep(1)

        self.automation.select_option(
            "oleg_fellappbundle_fellowshipapplication_trainings_4_geoLocation_country", "CSS_SELECTOR",
            "#select2-drop .select2-input",
            fellapp["fellowship_specialty"][4]
        )
        time.sleep(1)

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


def main():
    url = "https://view.online/c/demo-institution/demo-department/directory/login"
    username_text = "administrator"
    password_text = "1234567890_demo"
    automation = WebAutomation()
    automation.login_to_site(url, username_text, password_text)

    fellapp = FellApp(automation)
    #fellapp.configs()
    #fellapp.set_site_settings()
    fellapp.create_fellapps()

    print("FellApp done!")

    automation.quit_driver()

if __name__ == "__main__":
    main()