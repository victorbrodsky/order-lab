from web_automation import WebAutomation
from users import Users
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.common.by import By
#from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support import expected_conditions as EC
#from selenium.webdriver.common.action_chains import ActionChains
import time
import datetime
from datetime import date
from dateutil.relativedelta import relativedelta
#from selenium.webdriver.support.expected_conditions import visibility_of_all_elements_located
from selenium.common.exceptions import NoSuchElementException



class ResApp:
    def __init__(self, automation):
        self.automation = automation
        self.users = Users(automation)
        self.existing_users = self.users.get_existing_users()

    def get_res_apps(self):
        users = []
        users.append({
            'type': '1',  # 'AP/CP'
            'firstName': 'Bilal ',
            'lastName': 'Taylor',
            'displayName': 'Bilal Taylor',
            'email': 'cinava@yahoo.com',
            'u2': '234',
            'medschool': 'Baylor College of Medicine'
        })
        users.append({
            'type': '1',  # 'AP/CP'
            'firstName': 'Elif',
            'lastName': 'Collier',
            'displayName': 'Elif Collier',
            'email': 'cinava@yahoo.com',
            'u2': '235',
            'medschool': 'Southem Medical University'
        })
        users.append({
            'type': '1',  # 'AP/CP'
            'firstName': 'Myrtle',
            'lastName': 'Santos',
            'displayName': 'Myrtle Santos',
            'email': 'cinava@yahoo.com',
            'u2': '236',
            'medschool': 'Harvard Medical School'
        })

        return users

    def configs(self):
        driver = self.automation.get_driver()
        #Add Residency Track: https://view.online/c/demo-institution/demo-department/directory/admin/list-manager/id/1/37
        resapp_type_url = self.automation.baseurl.rstrip('/') + '/' + "directory/admin/list/edit-by-listname/ResidencyTrackList".lstrip('/')
        driver.get(resapp_type_url)
        time.sleep(1)

        # Wait for the table to load
        wait = WebDriverWait(driver, 10)  # Adjust timeout as needed
        table = wait.until(EC.presence_of_element_located((By.CLASS_NAME, 'records_list')))

        # Locate the <td> with the exact text "AP/CP"
        try:
            target_td = table.find_element(By.XPATH, './/td[text()="AP/CP"]')
            if target_td:
                #print("<td> with text 'AP/CP' already exists.")
                #print("Class name of the <td> is:", target_td.get_attribute('class'))  # Print the class name of the <td>
                pass
            else:
                #create
                #print("Create a new entry AP/CP")
                create_link = table.find_element(By.XPATH, './/a[text()="Create a new entry"]')
                create_link.click()
                time.sleep(3)

                name = driver.find_element(By.ID, "oleg_userdirectorybundle_genericlist_list_name")
                name.send_keys("AP/CP")

                # #s2id_oleg_userdirectorybundle_genericlist_institution
                # self.automation.select_option("s2id_oleg_userdirectorybundle_genericlist_institution", "CSS_SELECTOR",
                #                               ".select2-search .select2-input",
                #                               "Pathology and Laboratory Medicine"
                #                               )

                name = driver.find_element(By.ID, "oleg_userdirectorybundle_genericlist_duration")
                name.send_keys("4")

                time.sleep(3)

                self.automation.click_button_by_id("oleg_userdirectorybundle_genericlist_submit")
        except:
            print("resapp configs: Unable to find or create")

        time.sleep(3)

        #Create residency type
        residency_type_url = self.automation.baseurl.rstrip('/') + '/' + "residency-applications/residency-types-settings".lstrip('/')
        driver.get(residency_type_url)
        time.sleep(3)

        try:
            # Try to find the element
            residency_type = driver.find_element("xpath", "//h4/a[contains(text(), 'AP/CP')]")
            #print("Element found!")
            # You can perform actions on the element here
            residency_type.click()
            time.sleep(3)

            users = self.users.get_users()

            # add coordinator
            coordinator = users[2]

            # s2id_oleg_fellappbundle_fellowshipSubspecialty_coordinators
            self.automation.select_option("s2id_oleg_resappbundle_residencytracklist_coordinators", "CSS_SELECTOR",
                                          ".select2-choices .select2-input",
                                          coordinator["displayName"]
                                          )

            time.sleep(3)

            driver.execute_script("document.getElementById('select2-drop-mask').style.display = 'none';")

            # click Update button btn btn-warning
            self.automation.click_button("btn-warning")
        except NoSuchElementException:
            # create new fellowship type "AP/CP"
            print("residency track AP/CP does not exist")
            pass

        time.sleep(3)

    def create_resapps(self):
        for resapp in self.get_res_apps():
            self.create_single_resapp(resapp)
            #break

    def create_single_resapp(self, resapp):
        driver = self.automation.get_driver()
        url = self.automation.baseurl.rstrip('/') + '/' + "residency-applications/new/".lstrip('/')
        driver.get(url)
        time.sleep(1)

        #Create a new fellapp https://view.online/c/demo-institution/demo-department/fellowship-applications/new/
        #print("create new residency application")

        applicant_data_element = driver.find_element(By.CSS_SELECTOR,
                                                     "h4.panel-title > a[href='#residencyApplicantData']")
        applicant_data_element.click()
        time.sleep(3)

        self.automation.select_option(
            "s2id_oleg_resappbundle_residencyapplication_residencyTrack", "CSS_SELECTOR",
            "#select2-drop .select2-input",
            "AP/CP"
        )
        time.sleep(3)

        #oleg_resappbundle_residencyapplication_applicationSeasonStartDate => 07/01/2024
        start_date = '07/01/2024'
        datepicker = driver.find_element(By.ID, "oleg_resappbundle_residencyapplication_applicationSeasonStartDate")
        datepicker.clear()
        datepicker.send_keys(start_date)

        #oleg_resappbundle_residencyapplication_applicationSeasonEndDate => 06/30/2025
        start_date = '06/30/2025'
        datepicker = driver.find_element(By.ID, "oleg_resappbundle_residencyapplication_applicationSeasonEndDate")
        datepicker.clear()
        datepicker.send_keys(start_date)

        #oleg_fellappbundle_fellowshipapplication_user_infos_0_firstName
        first_name = driver.find_element(By.ID, "oleg_resappbundle_residencyapplication_user_infos_0_firstName")
        first_name.send_keys(resapp["firstName"])
        #oleg_fellappbundle_fellowshipapplication_user_infos_0_lastName
        last_name = driver.find_element(By.ID, "oleg_resappbundle_residencyapplication_user_infos_0_lastName")
        last_name.send_keys(resapp["lastName"])
        #oleg_fellappbundle_fellowshipapplication_user_infos_0_email
        email = driver.find_element(By.ID, "oleg_resappbundle_residencyapplication_user_infos_0_email")
        email.send_keys(resapp["email"])

        # #Open section #residencyQuestionnaireResponses
        # questionnaire = driver.find_element(By.CSS_SELECTOR,
        #                                              "h4.panel-title > a[href='#residencyQuestionnaireResponses']")
        # time.sleep(3)
        time.sleep(3)

        # oleg_resappbundle_residencyapplication_examinations_0_USMLEStep2CKScore
        ck_score = driver.find_element(By.ID, "oleg_resappbundle_residencyapplication_examinations_0_USMLEStep2CKScore")
        ck_score.send_keys(resapp["u2"])
        time.sleep(3)

        ####### Add Institution ########
        self.automation.select_option(
            "s2id_oleg_resappbundle_residencyapplication_trainings_0_institution", "CSS_SELECTOR",
            "#select2-drop .select2-search .select2-input",
            resapp["medschool"]
            #"Weill Cornell Medical College"
        )

        # self.automation.select_option(
        #     "s2id_oleg_resappbundle_residencyapplication_trainings_0_institution", "ID",
        #     "s2id_autogen11_search",
        #     # resapp["medschool"]
        #     "Weill Cornell Medical College"
        # )

        # dropdown_trigger = driver.find_element(By.ID, "s2id_oleg_resappbundle_residencyapplication_trainings_0_institution")  # Replace with the ID of the dropdown trigger
        # time.sleep(3)
        # dropdown_trigger.click()
        # time.sleep(3)
        # label_element = driver.find_element(By.XPATH, "//label[text()='Educational Institution:']")
        # # Use the 'for' attribute of the label to find the input element
        # input_id = label_element.get_attribute("for")
        # input_element = driver.find_element(By.ID, input_id)
        # time.sleep(3)
        # # Interact with the input field
        # input_element.send_keys("Weill Cornell Medical College")

        # results = driver.find_elements(By.CSS_SELECTOR, "ul#select2-results-10 li.select2-result-selectable")
        # for result in results:
        #     if "Weill Cornell Medical College" in result.text:
        #         print("result.text=",result.text)
        #         result.click()
        #         break
        #search_box = driver.find_element(By.ID, "s2id_autogen10_search")
        #search_box.send_keys("Weill Cornell Medical College")  # Type the desired institution name

        # for result in results:
        #     if "Weill Cornell Medical College" in result.text:
        #         result.click()
        #         break

        # #enable input: oleg_resappbundle_residencyapplication_trainings_0_institution
        # inst = driver.find_element(By.ID, "oleg_resappbundle_residencyapplication_trainings_0_institution")
        # time.sleep(3)
        # if inst.is_displayed():
        #     print("Element is visible.")
        # else:
        #     print("Element is not visible.")
        #     time.sleep(3)
        #     #driver.execute_script("arguments[0].scrollIntoView(true);", inst)
        #     driver.execute_script("arguments[0].style.display = 'block';", inst)
        #
        # time.sleep(3)
        # print("Enable element.")
        # driver.execute_script("arguments[0].removeAttribute('disabled'); arguments[0].removeAttribute('readonly');",
        #                       inst)
        # time.sleep(3)
        # # Click elsewhere (like the body or a parent element)
        # driver.find_element(By.ID, "oleg_resappbundle_residencyapplication_examinations_0_USMLEStep1Score").click()
        # time.sleep(3)
        # # Then interact with the input field
        # inst = driver.find_element(By.ID, "oleg_resappbundle_residencyapplication_trainings_0_institution")
        # inst.send_keys("Weill Cornell Medical College")

        ####### EOF Add Institution ########


        time.sleep(3)

        today = date.today()
        # Add one year
        one_year_plus = today + relativedelta(years=1)
        one_year_plus_str = one_year_plus.strftime("%m-%d-%Y")
        #print("one_year_plus_str=", one_year_plus_str)
        datepicker = driver.find_element(By.ID, "oleg_resappbundle_residencyapplication_trainings_0_completionDate")
        # Clear the field and enter the calculated date
        datepicker.clear()
        datepicker.send_keys(one_year_plus_str)

        #click submit btn-warning
        self.automation.click_button("btn-warning")

        #print("Finish new resapp")
        time.sleep(10)


def main():
    url = None
    username_text = "administrator"
    password_text = "1234567890"
    baseurl = "https://view.online/c/demo-institution/demo-department"
    automation = WebAutomation(baseurl, False)
    automation.login_to_site(url, username_text, password_text)

    resapp = ResApp(automation)
    #resapp.configs()
    resapp.create_resapps()

    print("ResApp done!")

    automation.quit_driver()

if __name__ == "__main__":
    main()