from web_automation import WebAutomation
from users import Users
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.action_chains import ActionChains
import time
from datetime import date
from dateutil.relativedelta import relativedelta
from selenium.webdriver.support.expected_conditions import visibility_of_all_elements_located


class FellApp:
    def __init__(self, automation):
        self.automation = automation
        self.users = Users(automation)
        self.existing_users = self.users.get_existing_users()

    def configs(self):
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
            target_td = table.find_element(By.XPATH, './/td[text()="Clinical Informatics"]')
            print("Found <td> with text 'Clinical Informatics'.")
            print("Class name of the <td> is:", target_td.get_attribute('class'))  # Print the class name of the <td>

            # Example action: Click the <td> (if clickable)
            target_td.click()
        except:
            print("<td> with text 'Clinical Informatics' not found.")
        time.sleep(10)

    def create_fellapps(self):
        for fellapp in self.get_fell_apps():
            self.create_single_fellapp(fellapp)
            break

    def create_single_fellapp(self):
        driver = self.automation.get_driver()
        url = "https://view.online/c/demo-institution/demo-department/call-log-book/entry/new"
        driver.get(url)
        time.sleep(1)

def main():
    url = "https://view.online/c/demo-institution/demo-department/directory/login"
    username_text = "administrator"
    password_text = "1234567890_demo"
    automation = WebAutomation()
    automation.login_to_site(url, username_text, password_text)

    fellapp = FellApp(automation)
    fellapp.configs()
    #fellapp.create_calllogs()

    print("FellApp done!")

    automation.quit_driver()

if __name__ == "__main__":
    main()