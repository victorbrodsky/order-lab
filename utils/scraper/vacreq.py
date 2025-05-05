from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
import time
import datetime
from selenium.webdriver.common.action_chains import ActionChains
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from web_automation import WebAutomation
from selenium.webdriver.common.alert import Alert
from selenium.common.exceptions import UnexpectedAlertPresentException
from users import Users



class VacReq:
    def __init__(self, automation):
        self.automation = automation
        self.users = Users(automation)

        #pass

    def create_group(self):
        #automation = WebAutomation()
        driver = self.automation.get_driver()
        url = "https://view.online/c/demo-institution/demo-department/time-away-request/add-group"
        driver.get(url)
        time.sleep(1)

        try:
            #s2id_oleg_vacreqbundle_group_approvaltype
            self.automation.select_option("s2id_oleg_vacreqbundle_group_approvaltype", "ID", "s2id_autogen1_search", "Faculty")

            #Pathology and Laboratory Medicine
            self.automation.select_option("s2id_oleg_vacreqbundle_group_institution", "ID", "s2id_autogen2_search",
                                          "Weill Cornell Medical College")
            time.sleep(3)
            alert = driver.switch_to.alert
            alert.accept()  # Clicks 'OK'

            self.automation.select_option("s2id_oleg_vacreqbundle_group_institution", "CSS_SELECTOR", "#select2-drop .select2-input",
                                          "Pathology and Laboratory Medicine")
            time.sleep(3)
            alert = driver.switch_to.alert
            alert.accept()  # Clicks 'OK'

            time.sleep(3)

            self.automation.click_button("btn-info")
            time.sleep(3)

        except Exception as e:
            print(f"An error occurred: {e}")

    def add_user_to_group(self):
        driver = self.automation.get_driver()
        url = "https://view.online/c/demo-institution/demo-department/time-away-request/groups/"
        driver.get(url)
        time.sleep(1)

        #self.automation.click_button("btn-info")
        # button = WebDriverWait(driver, 10).until(
        #     EC.element_to_be_clickable((By.XPATH, "//button[a(text(), 'Manage')]"))
        # )
        link = WebDriverWait(driver, 10).until(
            EC.element_to_be_clickable(
                (By.XPATH, "//a[contains(text(), 'Manage') and contains(@class, 'btn-sm btn-info')]"))
        )
        link.click()
        time.sleep(3)

        # Add approver
        try:
            if 1:
                print("Adding approver administrator")
                #Add approver
                # self.automation.select_option("s2id_oleg_vacreqbundle_user_participants_users", "CSS_SELECTOR",
                #                               "#vacreq-organizational-group-approver .select2-input",
                #                               "administrator")
                active_input = driver.find_element(
                    By.XPATH,
                    "//div[@id='vacreq-organizational-group-approver']//input[not(@disabled)]"
                )
                actions = ActionChains(driver)
                actions.move_to_element(active_input).click().perform()
                time.sleep(1)
                active_input.send_keys("administrator")
                time.sleep(1)
                active_input.send_keys(Keys.ENTER)
                time.sleep(1)
                button = WebDriverWait(driver, 10).until(
                    EC.element_to_be_clickable((By.XPATH, "//button[contains(text(), 'Add Approver(s)')]"))
                )
                button.click()
                print("Button Add Approver(s) clicked after waiting!")
                time.sleep(1)

        except Exception as e:
            print(f"An error occurred in add_user_to_group approver: {e}")

        # Add submitter
        try:
            if 1:
                for user in self.users.get_users():
                    print(f"Adding submitter: {user['displayName']}")

                    #Method 1
                    if 0:
                        active_input = driver.find_element(
                            By.XPATH,
                            "//div[@id='vacreq-organizational-group-submitter']//input[not(@disabled)]"
                        )
                        time.sleep(1)
                        actions = ActionChains(driver)
                        time.sleep(1)
                        actions.move_to_element(active_input).click().perform()
                        time.sleep(1)
                        active_input.send_keys(user['displayName'])
                        time.sleep(1)
                        active_input.send_keys(Keys.ENTER)

                    #Method 2
                    if 1:
                        parent_div = driver.find_element(By.ID, "vacreq-organizational-group-submitter")
                        child_div = parent_div.find_element(By.CLASS_NAME, "s2id_oleg_vacreqbundle_user_participants_users")
                        child_div.click()
                        child_input_div = parent_div.find_element(By.ID, "s2id_autogen3")
                        child_input_div.send_keys(user['displayName'])
                        time.sleep(1)
                        active_input.send_keys(Keys.ENTER)

                    time.sleep(1)
                    button = WebDriverWait(driver, 10).until(
                        EC.element_to_be_clickable((By.XPATH, "//button[contains(text(), 'Add Submitter(s)')]"))
                    )
                    button.click()
                    time.sleep(3)
                    print("Button Add Submitter(s) clicked after waiting!")
                    #break
        except Exception as e:
            print(f"An error occurred in add_user_to_group submitter: {e}")

        print("EOF add_user_to_group")

    def create_vacreqs(self):
        """Main function to execute all actions."""
        driver = self.automation.get_driver()
        # Set implicit wait
        driver.implicitly_wait(10)  # seconds

        for user in self.users.get_users():
            self.create_single_vacreq(user);
            #break


    def create_single_vacreq(self, user):
        """Main function to execute all actions."""
        driver = self.automation.get_driver()
        # Set implicit wait
        driver.implicitly_wait(10)  # seconds

        try:
            # Create a new vacation request
            url = "https://view.online/c/demo-institution/demo-department/time-away-request/"
            driver.get(url)
            # select_option(driver, "s2id_oleg_vacreqbundle_request_institution", "select2-input", "Pathology and Laboratory Medicine ID#29 (for review by administrator)")

            #         dropdown = driver.find_element(By.ID, "s2id_oleg_vacreqbundle_request_institution")
            #         driver.execute_script("arguments[0].scrollIntoView(true);", dropdown)
            #         actions = ActionChains(driver)
            #         actions.move_to_element(dropdown).click().perform()
            #         time.sleep(3)
            #         # Locate the search box and interact with it
            #         search_box = driver.find_element(By.CLASS_NAME, "select2-input")
            #         time.sleep(3)
            #         search_box.send_keys("Pathology and Laboratory Medicine ID#29 (for review by administrator)")
            #         time.sleep(3)
            #         search_box.send_keys(Keys.ENTER)

            # script = """
            #     var selectElement = document.getElementById('oleg_vacreqbundle_request_institution');
            #     selectElement.value = '29';  // Corresponds to "Pathology and Laboratory Medicine"
            #     var event = new Event('change', { bubbles: true });
            #     selectElement.dispatchEvent(event);
            #     """
            # driver.execute_script(script)

            self.automation.select_option("s2id_oleg_vacreqbundle_request_institution", "CSS_SELECTOR",
                                          "#select2-drop .select2-input",
                                          "Pathology and Laboratory Medicine")
            time.sleep(3)

            # combobox = driver.find_element(By.ID, "s2id_oleg_vacreqbundle_request_institution")
            # combobox.click()
            # search_box = driver.find_element(By.CLASS_NAME, "select2-choice")
            # search_box.send_keys("Pathology and Laboratory Medicine ID#29 (for review by administrator)")
            # search_box.send_keys(Keys.ENTER)

            #         time.sleep(3)
            #
            #         # Set implicit wait
            #         driver.implicitly_wait(10)  # seconds
            #
            # select_option(driver, "s2id_oleg_vacreqbundle_request_user", "select2-choice", "John Doe - johndoe (Local User)")
            # Click the Select2 dropdown to activate it
            #         dropdown = driver.find_element(By.ID, "s2id_oleg_vacreqbundle_request_user")
            #         dropdown.click()
            #         time.sleep(2)
            #         # Locate the search input within the Select2 dropdown
            #         search_box = driver.find_element(By.CLASS_NAME, "select2-input")
            #         time.sleep(2)
            #         # Type "John Doe" and press Enter
            #         search_box.send_keys("John Doe - johndoe (Local User)")
            #         time.sleep(2)
            #         search_box.send_keys(Keys.ENTER)

            # script = """
            #     var selectElement = document.querySelector('#oleg_vacreqbundle_request_user');
            #     selectElement.value = '12';  // Corresponds to "John Doe"
            #     var event = new Event('change', { bubbles: true });
            #     selectElement.dispatchEvent(event);
            #     """
            # driver.execute_script(script)

            self.automation.select_option("s2id_oleg_vacreqbundle_request_user", "CSS_SELECTOR",
                                          "#select2-drop .select2-input",
                                          #"John Doe"
                                          user['displayName']
                                          )
            time.sleep(3)

            # Select start date
            # Calculate the date for 1 week ago
            one_week_ahead = (datetime.date.today() + datetime.timedelta(days=7)).strftime("%m-%d-%Y")  # "%Y-%m-%d"
            #print("one_week_ago=", one_week_ago)
            # Find the datepicker input field
            datepicker = driver.find_element(By.ID, "oleg_vacreqbundle_request_requestVacation_startDate")
            # Clear the field and enter the calculated date
            datepicker.clear()
            datepicker.send_keys(one_week_ahead)

            time.sleep(5)

            datepicker = driver.find_element(By.ID, "oleg_vacreqbundle_request_requestVacation_endDate")
            # Clear the field and enter the calculated date
            datepicker.clear()
            datepicker.send_keys(one_week_ahead)
            time.sleep(5)

            number_of_days_field = driver.find_element(By.ID, "oleg_vacreqbundle_request_requestVacation_numberOfDays")
            # number_of_days_field.click()
            driver.execute_script("arguments[0].scrollIntoView(true);", number_of_days_field)
            number_of_days_field.clear()
            # number_of_days_field.send_keys('1')
            driver.execute_script("arguments[0].value = arguments[1];", number_of_days_field, "1")
            # days.click();
            time.sleep(5)

            # calculate_button = driver.find_element(By.CLASS_NAME, "calculate-btn")
            # Click the "Calculate" button
            # calculate_button.click()

            # Optional: Wait to observe the action
            # time.sleep(3)

            button = driver.find_element(By.ID, "btnCreateVacReq")
            button.click()

            time.sleep(5)


        finally:
            # driver.quit()
            pass

def main():
    url = "https://view.online/c/demo-institution/demo-department/directory/login"
    username_text = "administrator"
    password_text = "1234567890_demo"
    automation = WebAutomation()
    automation.login_to_site(url, username_text, password_text)

    vacreq = VacReq(automation)
    vacreq.create_group()
    vacreq.add_user_to_group()
    vacreq.create_vacreqs()
    print("Vacation Request done!")

    automation.quit_driver()

# Execute the main function
if __name__ == "__main__":
    main()