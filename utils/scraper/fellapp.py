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

        wait = WebDriverWait(driver, 10)  # Adjust timeout as needed
        rows = wait.until(EC.presence_of_all_elements_located(
            (By.CLASS_NAME, 'treenode')))  # Locate all rows with the class name 'treenode'

        # Check if any row contains the text "Clinical Informatics"
        found = False
        for row in rows:
            if "Clinical Informatics" in row.text:
                found = True
                break

        if found:
            print("Row with 'Clinical Informatics' exists.")
        else:
            print("Row with 'Clinical Informatics' does not exist.")

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