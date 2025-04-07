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

    def get_fell_apps(self):
        users = []
        users.append({
            'type': '1',  # 'Clinical Informatics'
            'firstName': 'Joe',
            'lastName': 'Simpson',
            'displayName': 'Joe Simpson',
            'email': 'cinava@yahoo.com'
        })
        users.append({
            'type': '1',  # 'Clinical Informatics'
            'firstName': 'Soleil',
            'lastName': 'Teresia',
            'displayName': 'Soleil Teresia',
            'email': 'cinava@yahoo.com'
        })
        users.append({
            'type': '1',  # 'Clinical Informatics'
            'firstName': 'Haides',
            'lastName': 'Neon',
            'displayName': 'Haides Neon',
            'email': 'cinava@yahoo.com'
        })

        return users

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
            if target_td:
                print("<td> with text 'Clinical Informatics' already exists.")
                #print("Class name of the <td> is:", target_td.get_attribute('class'))  # Print the class name of the <td>
                #pass
            else:
                #create
                create_link = table.find_element(By.XPATH, './/a[text()="Create a new entry"]')
                create_link.click()
                time.sleep(3)

                name = driver.find_element(By.ID, "oleg_userdirectorybundle_genericlist_list_name")
                name.send_keys("Clinical Informatics")

                #s2id_oleg_userdirectorybundle_genericlist_institution
                self.automation.select_option("s2id_oleg_userdirectorybundle_genericlist_institution", "CSS_SELECTOR",
                                              ".select2-search .select2-input",
                                              "Pathology and Laboratory Medicine"
                                              )
                time.sleep(3)

                self.automation.click_button_by_id("oleg_userdirectorybundle_genericlist_submit")

        except:
            print("Unable to find or create")

        time.sleep(3)

        #Create fellowship type
        fellowship_type_url = "https://view.online/c/demo-institution/demo-department/fellowship-applications/fellowship-types-settings"
        driver.get(fellowship_type_url)

        #<a href="/c/demo-institution/demo-department/fellowship-applications/fellowship-type/edit/1">Clinical Informatics</a>
        #fellowship_type = table.find_element(By.XPATH, './/a[text()="Clinical Informatics"]')
        fellowship_type = driver.find_element("xpath", "//h4/a[contains(text(), 'Clinical Informatics')]")
        if fellowship_type:
            fellowship_type.click()
            time.sleep(3)

            users = self.users.get_users()

            #add coordinator
            coordinator = users[2]

            #s2id_oleg_fellappbundle_fellowshipSubspecialty_coordinators
            self.automation.select_option("s2id_oleg_fellappbundle_fellowshipSubspecialty_coordinators", "CSS_SELECTOR",
                                          ".select2-choices .select2-input",
                                          coordinator["displayName"]
                                          )

            time.sleep(3)

            WebDriverWait(driver, 10).until(
                EC.invisibility_of_element((By.ID, "select2-drop-mask"))
            )

            #click Update button btn btn-warning
            self.automation.click_button("btn-warning")

        else:
            #create new fellowship type "Clinical Informatics"
            self.automation.click_button("btn-primary")
            time.sleep(3)

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