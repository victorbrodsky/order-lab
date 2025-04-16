from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import Select
import time
from selenium.common.exceptions import NoSuchElementException
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
        username_element = driver.find_element(By.ID, "display-username")
        print("Element with ID 'display-username' exists on the page.")
        if username_element:
            print("Login page displayed, try to re-login. ")
            username_text = "administrator"
            password_text = "1234567890_demo"
            self.automation.login_to_site(url, username_text, password_text)
            time.sleep(3)
            username_element = driver.find_element(By.ID, "display-username")
            if username_element:
                print("Element with ID 'display-username' still exist on the page.")
                return
        else:
            print("Not login page, continue")


        #oleg_userdirectorybundle_initialconfigurationtype_environment
        # self.automation.select_option("oleg_userdirectorybundle_initialconfigurationtype_environment", "CSS_SELECTOR",
        #                               "#select2-drop .select2-input",
        #                               "Pathology and Laboratory Medicine")

        #if page with init displayed
        time.sleep(3)
        # Locate the <select> element by its ID
        select_element = driver.find_element(By.ID, "oleg_userdirectorybundle_initialconfigurationtype_environment")
        if select_element:
            # Create a Select object
            select = Select(select_element)
            # Select the option with value "demo"
            select.select_by_value("demo")
            # Alternatively, select by visible text
            # select.select_by_visible_text("demo")
            time.sleep(3)

            select_element = driver.find_element(By.ID, "oleg_userdirectorybundle_initialconfigurationtype_urlConnectionChannel")
            select = Select(select_element)
            select.select_by_value("https")
            time.sleep(3)

            #oleg_userdirectorybundle_initialconfigurationtype_mailerUser
            password_text = self.driver.find_element(By.ID, "oleg_userdirectorybundle_initialconfigurationtype_password_first")
            password_text.send_keys("1234567890_demo")
            time.sleep(3)
            password_text = self.driver.find_element(By.ID, "oleg_userdirectorybundle_initialconfigurationtype_password_second")
            password_text.send_keys("1234567890_demo")
            time.sleep(3)

            siteEmail = 'oli2002@med.cornell.edu'
            #oleg_userdirectorybundle_initialconfigurationtype_siteEmail
            password_text = self.driver.find_element(By.ID,
                                                     "oleg_userdirectorybundle_initialconfigurationtype_siteEmail")
            password_text.send_keys(siteEmail)
            time.sleep(3)

            #oleg_userdirectorybundle_initialconfigurationtype_mailerDeliveryAddresses
            password_text = self.driver.find_element(By.ID,
                                                     "oleg_userdirectorybundle_initialconfigurationtype_mailerDeliveryAddresses")
            password_text.send_keys(siteEmail)
            time.sleep(3)

            #click button by ID: oleg_userdirectorybundle_initialconfigurationtype_save
            self.automation.click_button_by_id("oleg_userdirectorybundle_initialconfigurationtype_save")
            time.sleep(3)


    def run_site_settngs(self):
        driver = self.automation.get_driver()

        #url = "https://view.online/c/demo-institution/demo-department/directory/settings/"
        #driver.get(url)
        #time.sleep(3)

        self.open_misc_panel()
        url = "https://view.online/c/demo-institution/demo-department/directory/admin/populate-country-city-list-with-default-values"
        driver.get(url)
        time.sleep(10)

        self.open_misc_panel()
        url = "https://view.online/c/demo-institution/demo-department/directory/admin/populate-all-lists-with-default-values"
        driver.get(url)
        time.sleep(10)

        self.open_misc_panel()
        url = "https://view.online/c/demo-institution/demo-department/scan/admin/populate-all-lists-with-default-values"
        driver.get(url)
        time.sleep(10)

        self.open_misc_panel()
        url = "https://view.online/c/demo-institution/demo-department/translational-research/generate-antibody-list/ihc_antibody_postgresql.sql"
        driver.get(url)
        time.sleep(10)

        self.open_misc_panel()
        url = "https://view.online/c/demo-institution/demo-department/directory/admin/list/generate-form-node-tree/"
        driver.get(url)
        time.sleep(10)

        self.open_misc_panel()
        url = "https://view.online/c/demo-institution/demo-department/directory/admin/generate-dermatopathology-form-node-tree/"
        driver.get(url)
        time.sleep(10)

        self.open_misc_panel()
        url = "https://view.online/c/demo-institution/demo-department/directory/fellowship-applications/create-default-fellowship-type"
        driver.get(url)
        time.sleep(10)

        self.open_misc_panel()
        url = "https://view.online/c/demo-institution/demo-department/time-away-request/generate-default-group"
        driver.get(url)
        time.sleep(10)

    def open_misc_panel(self):
        driver = self.automation.get_driver()
        panel_toggle = driver.find_element(By.XPATH,
                                           "//div[@class='panel-heading']/h4[@class='panel-title text-left']/a[@data-toggle='collapse']")
        panel_toggle.click()


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