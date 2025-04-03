from web_automation import WebAutomation
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.action_chains import ActionChains
import time
from datetime import date
from dateutil.relativedelta import relativedelta


class CallLog:
    def __init__(self, automation):
        self.automation = automation
        #self.users = Users(automation)
        #self.existing_users = self.users.get_existing_users()

    def get_call_logs(self) -> list[dict]:
        callogs = [
            {
                'mrntype': '1',
                'mrn': '1457',
                'firstName': 'Andre',
                'lastName': 'Castro',
                'dob': '02/20/1985',
                'service': 'Transfusion Medicine',
                'history': (
                    'Splenectomized patient with beta thalassemia major on Luspatercept, '
                    'transfused every 3 weeks with 1-2 units red cells to maintain pre-transfusion '
                    'hemoglobin of 9.5-10.5 g/dL. Patient blood type is O+. '
                    'Unexpected antibodies: anti-I, non-spec, PAN, anti-V and warm autoantibody. '
                    'Special needs: E neg, K neg, HbS-'
                )
            },
            {
                'mrntype': '1',
                'mrn': '1657',
                'firstName': 'Callum',
                'lastName': 'Cruz',
                'dob': '07/25/1965',
                'service': 'Microbiology',
                'history': (
                    'This patient with a past medical history of myelodysplastic syndrome '
                    'with excess blasts transformed to acute myeloblastic leukemia (diagnosed in 2021) '
                    'with relapse in Dec 2022, anemia, coronary artery disease status '
                    'post circumflex angioplasty in 2016, and hypertension'
                )
            },
            {
                'mrntype': '1',
                'mrn': '1867',
                'firstName': 'Hugo',
                'lastName': 'Ortiz',
                'dob': '11/25/1955',
                'service': 'Coagulation',
                'history': (
                    'Paged by BB, work up complete. No abnormal findings. Ok to release further products. SafeTrace updated.'
                )
            }
        ]
        return callogs

    def create_calllogs(self) -> None:
        for calllog in self.get_call_logs():
            self.create_single_calllog(calllog)
            break

    def create_single_calllog(self, calllog):
        driver = self.automation.get_driver()
        url = "https://view.online/c/demo-institution/demo-department/call-log-book/entry/new"
        driver.get(url)
        time.sleep(1)

        # $client->waitForVisibility('oleg_calllogformbundle_messagetype[patient][0][dob][0][field]');
        #oleg_calllogformbundle_messagetype_patient_0_mrn_0_field
        time.sleep(3)
        mrn = driver.find_element(By.ID, "oleg_calllogformbundle_messagetype_patient_0_mrn_0_field")
        mrn.send_keys(calllog['mrn'])
        time.sleep(3)

        #oleg_calllogformbundle_messagetype_patient_0_dob_0_field
        dob = driver.find_element(By.ID, "oleg_calllogformbundle_messagetype_patient_0_dob_0_field")
        dob.send_keys(calllog['dob'])

        lastname = driver.find_element(By.ID, "oleg_calllogformbundle_messagetype_patient_0_encounter_0_patlastname_0_field")
        lastname.send_keys(calllog['lastName'])

        firstname = driver.find_element(By.ID,
                                       "oleg_calllogformbundle_messagetype_patient_0_encounter_0_patfirstname_0_field")
        firstname.send_keys(calllog['firstName'])
        time.sleep(3)

        #search_patient_button
        self.automation.click_button_by_id("search_patient_button")
        time.sleep(3)

        #self.automation.click_button_by_id("addnew_patient_button")
        button = driver.find_element(By.ID, "addnew_patient_button")
        if button.is_displayed():
            self.automation.click_button_by_id("addnew_patient_button")
            time.sleep(3)
            # Switch to the alert and accept it
            alert = driver.switch_to.alert
            print(f"Alert text: {alert.text}")  # Optional: Get the text of the alert
            alert.accept()  # Click "OK" to accept the confirmation box

        print("Filling out new call log")
        # self.automation.select_option("s2id_oleg_calllogformbundle_messagetype_messageCategory",
        #                               "CSS_SELECTOR",
        #                               ".select2-search .select2-input",
        #                               calllog['service']
        #                               )
        # # Locate the label element with text containing "Service"
        # label = driver.find_element(By.XPATH, "//label[contains(text(), 'Service')]")
        # # Extract the value of the 'for' attribute (associates the label with the element's ID)
        # target_id = label.get_attribute('for')
        # # Use the extracted ID to locate the desired element
        # service = driver.find_element(By.ID, target_id)
        # actions = ActionChains(driver)
        # actions.move_to_element(service).click().perform()
        # time.sleep(1)
        # search_box = driver.find_element(By.CSS_SELECTOR, ".select2-search .select2-input")
        # search_box.send_keys(calllog['service'])
        # time.sleep(1)
        # search_box.send_keys(Keys.ENTER)
        # # time.sleep(3)

        # time.sleep(3)
        # blocking_element = driver.find_element(By.ID, "user-headroom-header")
        # driver.execute_script("arguments[0].style.display = 'none';", blocking_element)
        #
        # element = driver.find_element(By.ID, "s2id_oleg_calllogformbundle_messagetype_messageCategory")
        # time.sleep(3)
        # driver.execute_script("arguments[0].scrollIntoView();", element)
        # element.click()
        # time.sleep(3)
        # search_box = driver.find_element(By.CSS_SELECTOR, ".select2-search .select2-input") #or #select2-drop .select2-input
        # time.sleep(3)
        # search_box.send_keys('Transfusion')

        #select2-result-label-65
        # script = f"""
        #             $("#s2id_oleg_calllogformbundle_messagetype_messageCategory").select2('val','1');
        #         """
        # script = """
        #             $(".ajax-combobox-messageCategory").click();
        #         """
        # driver.execute_script(script)

        parent_element = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.CLASS_NAME, 'composite-tree-holder'))
        )
        time.sleep(3)
        # Find child elements after ensuring the parent is present
        matching_elements = parent_element.find_elements(By.CLASS_NAME,'ajax-combobox-messageCategory')
        count = len(matching_elements)
        print(f"Number of matching elements: {count}")
        if matching_elements:
            last_element = matching_elements[-1]
            last_element.click()
        else:
            print("No matching elements found!")

        time.sleep(10)




def main():
    url = "https://view.online/c/demo-institution/demo-department/directory/login"
    username_text = "administrator"
    password_text = "1234567890_demo"
    automation = WebAutomation()
    automation.login_to_site(url, username_text, password_text)

    callog = CallLog(automation)
    callog.create_calllogs()

    print("CallLog done!")

    automation.quit_driver()

if __name__ == "__main__":
    main()