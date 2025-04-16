from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import Select
import time
from selenium.common.exceptions import NoSuchElementException
from selenium.common.exceptions import TimeoutException
from web_automation import WebAutomation


class Init:
    def __init__(self, automation):
        self.automation = automation

    def initialize(self):
        driver = self.automation.get_driver()
        url = "https://view.online/c/demo-institution/demo-department/directory/admin/first-time-login-generation-init/"
        #print("run init link")
        driver.get(url)
        #print("after run init link")
        time.sleep(3)

        #login using default username and password
        #self.automation.click_button("btn-primary")
        #time.sleep(3)
        username_text = "administrator"
        password_text = "1234567890"
        self.automation.login_to_site(None, username_text, password_text)
        time.sleep(3)

        #check if logged in successesfull "display-username"

        #locate the element by ID
        # username_element = driver.find_element(By.ID, "display-username")
        # print("Element with ID 'display-username' exists on the page.")
        # if username_element:
        #     print("Login page displayed, try to re-login. ")
        #     username_text = "administrator"
        #     password_text = "1234567890_demo"
        #     self.automation.login_to_site(None, username_text, password_text)
        #     time.sleep(3)
        #     username_element = driver.find_element(By.ID, "display-username")
        #     if username_element:
        #         print("Element with ID 'display-username' still exist on the page.")
        #         return
        # else:
        #     print("Not login page, continue")

        try:
            # Attempt to locate the element
            username_element = driver.find_element(By.ID, "display-username")
            if username_element:
                print("Element display-username exists => Login page => Re-login.")
                username_text = "administrator"
                password_text = "1234567890_demo"
                self.automation.login_to_site(None, username_text, password_text)
                time.sleep(3)
                try:
                    username_element = driver.find_element(By.ID, "display-username")
                    time.sleep(3)
                    if username_element:
                        print("Element display-username exists => Login page => Failed")
                        return False
                except NoSuchElementException:
                    print("Element does not exist => Logged in => Continue initializing.")
            else:
                print("Element display-username exists but is not interactable!")
                return False
        except NoSuchElementException:
            print("Element does not exist => Logged in => Continue initializing.")

        #oleg_userdirectorybundle_initialconfigurationtype_environment
        # self.automation.select_option("oleg_userdirectorybundle_initialconfigurationtype_environment", "CSS_SELECTOR",
        #                               "#select2-drop .select2-input",
        #                               "Pathology and Laboratory Medicine")

        #if page with init displayed
        print("Continue initializing.")
        time.sleep(3)
        try:
            select_element = driver.find_element(By.ID, "oleg_userdirectorybundle_initialconfigurationtype_environment")
            time.sleep(3)
            self.run_initializing()
        except NoSuchElementException:
            print("Initializing page is not showing. Continue with site settings.")

    def run_initializing(self):
        driver = self.automation.get_driver()
        select_element = driver.find_element(By.ID, "oleg_userdirectorybundle_initialconfigurationtype_environment")
        if select_element:
            # Create a Select object
            select = Select(select_element)
            # Select the option with value "demo"
            select.select_by_value("demo")
            # Alternatively, select by visible text
            # select.select_by_visible_text("demo")
            time.sleep(3)

            select_element = driver.find_element(By.ID,
                                                 "oleg_userdirectorybundle_initialconfigurationtype_urlConnectionChannel")
            select = Select(select_element)
            select.select_by_value("https")
            time.sleep(3)

            # oleg_userdirectorybundle_initialconfigurationtype_mailerUser
            password_text = self.driver.find_element(By.ID,
                                                     "oleg_userdirectorybundle_initialconfigurationtype_password_first")
            password_text.send_keys("1234567890_demo")
            time.sleep(3)
            password_text = self.driver.find_element(By.ID,
                                                     "oleg_userdirectorybundle_initialconfigurationtype_password_second")
            password_text.send_keys("1234567890_demo")
            time.sleep(3)

            siteEmail = 'oli2002@med.cornell.edu'
            # oleg_userdirectorybundle_initialconfigurationtype_siteEmail
            password_text = self.driver.find_element(By.ID,
                                                     "oleg_userdirectorybundle_initialconfigurationtype_siteEmail")
            password_text.send_keys(siteEmail)
            time.sleep(3)

            # oleg_userdirectorybundle_initialconfigurationtype_mailerDeliveryAddresses
            password_text = self.driver.find_element(By.ID,
                                                     "oleg_userdirectorybundle_initialconfigurationtype_mailerDeliveryAddresses")
            password_text.send_keys(siteEmail)
            time.sleep(3)

            # click button by ID: oleg_userdirectorybundle_initialconfigurationtype_save
            self.automation.click_button_by_id("oleg_userdirectorybundle_initialconfigurationtype_save")
            time.sleep(3)

    def run_site_settngs(self):
        driver = self.automation.get_driver()

        #url = "https://view.online/c/demo-institution/demo-department/directory/settings/"
        #driver.get(url)
        #time.sleep(3)

        #self.open_misc_panel()
        url = "https://view.online/c/demo-institution/demo-department/directory/admin/populate-country-city-list-with-default-values"
        #driver.get(url)
        #time.sleep(10)
        self.populate_url(url)

        #self.open_misc_panel()
        url = "https://view.online/c/demo-institution/demo-department/directory/admin/populate-all-lists-with-default-values"
        #driver.get(url)
        #time.sleep(10)
        self.populate_url(url)


        #self.open_misc_panel()
        url = "https://view.online/c/demo-institution/demo-department/scan/admin/populate-all-lists-with-default-values"
        #driver.get(url)
        #time.sleep(10)
        self.populate_url(url)

        #self.open_misc_panel()
        url = "https://view.online/c/demo-institution/demo-department/translational-research/generate-antibody-list/ihc_antibody_postgresql.sql"
        #driver.get(url)
        #time.sleep(10)
        self.populate_url(url)

        #self.open_misc_panel()
        url = "https://view.online/c/demo-institution/demo-department/directory/admin/list/generate-form-node-tree/"
        #driver.get(url)
        #time.sleep(10)
        self.populate_url(url)

        #self.open_misc_panel()
        url = "https://view.online/c/demo-institution/demo-department/directory/admin/generate-dermatopathology-form-node-tree/"
        #driver.get(url)
        #time.sleep(10)
        self.populate_url(url)

        #self.open_misc_panel()
        url = "https://view.online/c/demo-institution/demo-department/fellowship-applications/create-default-fellowship-type"
        #driver.get(url)
        #time.sleep(10)
        self.populate_url(url)

        #self.open_misc_panel()
        url = "https://view.online/c/demo-institution/demo-department/time-away-request/generate-default-group"
        #driver.get(url)
        #time.sleep(10)
        self.populate_url(url)

    def populate_url(self, url):
        time.sleep(10)
        print("Populate url=",url)
        driver = self.automation.get_driver()
        max_retries = 5
        retry_delay = 30  # Seconds
        for attempt in range(max_retries):
            try:
                #self.open_misc_panel()
                #time.sleep(1)
                driver.get(url)
                print("Page loaded successfully.")
                break
            except TimeoutException:
                print(f"Attempt {attempt + 1} failed. Retrying in {retry_delay} seconds...")
                time.sleep(retry_delay)

    def open_misc_panel(self):
        driver = self.automation.get_driver()
        #panel_toggle = driver.find_element(By.XPATH,
        #                                   "//div[@class='panel-heading']/h4[@class='panel-title text-left']/a[@data-toggle='collapse']")
        #panel_toggle.click()
        max_retries = 3
        retry_delay = 3  # Seconds
        for attempt in range(max_retries):
            try:
                panel_toggle = driver.find_element(By.XPATH, "//div[@class='panel-heading']/h4[@class='panel-title text-left']/a[@data-toggle='collapse']")
                time.sleep(1)
                panel_toggle.click()
                time.sleep(1)
                break
            except TimeoutException:
                print(f"Attempt {attempt + 1} failed. Retrying in {retry_delay} seconds...")
                time.sleep(retry_delay)
                url = "https://view.online/c/demo-institution/demo-department/directory/settings/"
                driver.get(url)
                time.sleep(3)


def main():
    #url = "https://view.online/c/demo-institution/demo-department/directory/admin/first-time-login-generation-init/"
    #username_text = "administrator"
    #password_text = "1234567890_demo"

    automation = WebAutomation()
    #driver = automation.initialize_driver()

    # Initialize using https://view.online/c/demo-institution/demo-department/directory/admin/first-time-login-generation-init/
    init = Init(automation)
    init.initialize()
    init.run_site_settngs()
    print("init done!")

if __name__ == "__main__":
    main()