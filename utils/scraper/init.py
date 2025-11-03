from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import Select
import time
from selenium.common.exceptions import NoSuchElementException
from selenium.common.exceptions import TimeoutException
from web_automation import WebAutomation
import subprocess
import getpass



class Init:
    def __init__(self, automation):
        self.automation = automation
        self.username = "administrator"
        self.password_default = "1234567890"
        self.password_new = "1234567890"

    # def initialize_orig(self):
    #     driver = self.automation.get_driver()
    #     url = "https://view.online/c/demo-institution/demo-department/directory/admin/first-time-login-generation-init/"
    #     print("run init link")
    #     driver.get(url)
    #     print("after run init link")
    #     time.sleep(3)
    #
    #     if "500 Internal Server Error" in driver.page_source:
    #         print("500 Error detected!")
    #         self.run_deploy_command()
    #         time.sleep(3)
    #
    #         #Second attempt to run init
    #         url = "https://view.online/c/demo-institution/demo-department/directory/admin/first-time-login-generation-init/"
    #         print("run init link 2")
    #         driver.get(url)
    #         print("after run init link 2")
    #         time.sleep(3)
    #
    #     #login using default username and password
    #     self.automation.login_to_site(None, self.username, self.password_default)
    #     time.sleep(3)
    #
    #     #check if logged in successful "display-username"
    #     try:
    #         # Attempt to locate the element
    #         username_element = driver.find_element(By.ID, "display-username")
    #         if username_element:
    #             print("Element display-username exists => Login page => Re-login.")
    #             self.automation.login_to_site(None, self.username, self.password_new)
    #             time.sleep(3)
    #             try:
    #                 username_element = driver.find_element(By.ID, "display-username")
    #                 time.sleep(3)
    #                 if username_element:
    #                     print("Element display-username exists => Login page => Failed")
    #                     return False
    #             except NoSuchElementException:
    #                 print("Element 'display-username' does not exist => Logged in => Continue initializing.")
    #         else:
    #             print("Element display-username exists but is not interactable!")
    #             return False
    #     except NoSuchElementException:
    #         print("Element does not exist => Logged in => Continue initializing.")
    #
    #     #if page with init displayed
    #     print("Continue initializing.")
    #     time.sleep(3)
    #     try:
    #         select_element = driver.find_element(By.ID, "oleg_userdirectorybundle_initialconfigurationtype_environment")
    #         time.sleep(3)
    #         self.config_initializing()
    #     except NoSuchElementException:
    #         print("Initializing page is not showing. Continue with site settings.")
    #
    #     return True

    def initialize(self):
        driver = self.automation.get_driver()
        url = "https://view.online/c/demo-institution/demo-department/directory/admin/first-time-login-generation-init/"
        print("run init link")
        driver.get(url)
        print("after run init link")
        time.sleep(3)

        if "500 Internal Server Error" in driver.page_source:
            print("500 Error detected!")
            self.run_deploy_command()
            time.sleep(60)

            #Second attempt to run init
            url = "https://view.online/c/demo-institution/demo-department/directory/admin/first-time-login-generation-init/"
            print("run init link 2")
            driver.get(url)
            print("after run init link 2")
            time.sleep(3)

        #login using default username and password
        self.automation.login_to_site(None, self.username, self.password_default)
        time.sleep(3)

        #if page with init displayed
        print("Continue initializing.")
        time.sleep(3)
        try:
            #oleg_userdirectorybundle_initialconfigurationtype_environment
            select_element = driver.find_element(By.ID, "oleg_userdirectorybundle_initialconfigurationtype_environment")
            time.sleep(3)
            self.config_initializing()
        except NoSuchElementException:
            driver.save_screenshot("init_page_error.png")
            print("Initializing page is not showing. Continue with site settings.")

        return True

    def config_initializing(self):
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
            password_text = driver.find_element(By.ID,
                                                     "oleg_userdirectorybundle_initialconfigurationtype_password_first")
            password_text.send_keys("1234567890")
            time.sleep(3)
            password_text = driver.find_element(By.ID,
                                                     "oleg_userdirectorybundle_initialconfigurationtype_password_second")
            password_text.send_keys("1234567890")
            time.sleep(3)

            siteEmail = 'oli2002@med.cornell.edu'
            # oleg_userdirectorybundle_initialconfigurationtype_siteEmail
            password_text = driver.find_element(By.ID,
                                                     "oleg_userdirectorybundle_initialconfigurationtype_siteEmail")
            password_text.send_keys(siteEmail)
            time.sleep(3)

            #Don't use "Reroute all outgoing emails only to " for Demo
            # # oleg_userdirectorybundle_initialconfigurationtype_mailerDeliveryAddresses
            # password_text = driver.find_element(By.ID,
            #                                          "oleg_userdirectorybundle_initialconfigurationtype_mailerDeliveryAddresses")
            # password_text.send_keys(siteEmail)
            # time.sleep(3)

            # click button by ID: oleg_userdirectorybundle_initialconfigurationtype_save
            self.automation.click_button_by_id("oleg_userdirectorybundle_initialconfigurationtype_save")
            time.sleep(3)

            #Modify footer
            #Demo Department at Demo Institution
            #Do it in the footer.html.twig globally iv env == 'demo'

            print("config_initializing complete")
            return True
        else:
            print("config_initializing failed. It looks like this is not an initial config page")
            return False

    def run_site_settngs(self):
        driver = self.automation.get_driver()

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
        url = "https://view.online/c/demo-institution/demo-department/directory/admin/list/generate-dermatopathology-form-node-tree/"
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

        print("All lists have been populated")
        return True

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

    def init_mailer(self, mailer_user, mailer_password):
        driver = self.automation.get_driver()
        #/c/demo-institution/demo-department/directory/settings/1/edit?param=mailerUser
        url = "https://view.online/c/demo-institution/demo-department/directory/settings/1/edit?param=mailerUser"
        driver.get(url)
        time.sleep(3)
        # set google mailer
        # Mailer username: view.online.administrator@pathologysystems.org
        #oleg_userdirectorybundle_siteparameters_mailerUser
        username = driver.find_element(By.ID, "oleg_userdirectorybundle_siteparameters_mailerUser")
        username.clear()
        time.sleep(1)
        print(f"Set mailer_user={mailer_user}")
        username.send_keys(mailer_user)
        time.sleep(1)
        self.automation.click_button_by_id("oleg_userdirectorybundle_siteparameters_submit")
        time.sleep(3)

        # Mailer password: "dfmg hhjs rwjk ywlm"
        url = "https://view.online/c/demo-institution/demo-department/directory/settings/1/edit?param=mailerPassword"
        driver.get(url)
        time.sleep(3)
        password = driver.find_element(By.ID, "oleg_userdirectorybundle_siteparameters_mailerPassword")
        time.sleep(1)
        password.clear()
        print(f"Set mailer_password={mailer_password}")
        password.send_keys(mailer_password)
        time.sleep(3)
        self.automation.click_button_by_id("oleg_userdirectorybundle_siteparameters_submit")
        time.sleep(3)

    def init_captcha(self,captcha_sitekey, captcha_secretkey):
        driver = self.automation.get_driver()
        # /c/demo-institution/demo-department/directory/settings/1/edit?param=mailerUser
        url = "https://view.online/c/demo-institution/demo-department/directory/settings/1/edit?param=captchaSiteKey"
        driver.get(url)
        time.sleep(3)
        # set google mailer
        captcha_sitekey_field = driver.find_element(By.ID, "oleg_userdirectorybundle_siteparameters_captchaSiteKey")
        captcha_sitekey_field.clear()
        time.sleep(1)
        print(f"Set captcha_sitekey={captcha_sitekey}")
        captcha_sitekey_field.send_keys(captcha_sitekey)
        time.sleep(1)
        self.automation.click_button_by_id("oleg_userdirectorybundle_siteparameters_submit")
        time.sleep(3)

        url = "https://view.online/c/demo-institution/demo-department/directory/settings/1/edit?param=captchaSecretKey"
        driver.get(url)
        time.sleep(3)
        # set google mailer
        captcha_secretkey_field = driver.find_element(By.ID, "oleg_userdirectorybundle_siteparameters_captchaSecretKey")
        captcha_secretkey_field.clear()
        time.sleep(1)
        print(f"Set captcha_sitekey={captcha_secretkey}")
        captcha_secretkey_field.send_keys(captcha_secretkey)
        time.sleep(1)
        self.automation.click_button_by_id("oleg_userdirectorybundle_siteparameters_submit")
        time.sleep(3)

        #check box
        url = "https://view.online/c/demo-institution/demo-department/directory/settings/1/edit?param=captchaEnabled"
        driver.get(url)
        time.sleep(3)
        checkbox = driver.find_element(By.ID, 'oleg_userdirectorybundle_siteparameters_captchaEnabled')
        checkbox.click()
        time.sleep(1)
        self.automation.click_button_by_id("oleg_userdirectorybundle_siteparameters_submit")
        time.sleep(3)

    def init_other_settings(self):
        driver = self.automation.get_driver()

        # Global academic start date
        url = "https://view.online/c/demo-institution/demo-department/directory/settings/1/edit?param=academicYearStart"
        driver.get(url)
        time.sleep(3)
        driver.save_screenshot("academicYearStart1.png")

        start_date_month = driver.find_element(By.ID, "oleg_userdirectorybundle_siteparameters_academicYearStart_month")
        time.sleep(1)

        driver.execute_script("arguments[0].scrollIntoView(true);", start_date_month)
        driver.save_screenshot("academicYearStart2.png")
        time.sleep(1)

        select = Select(start_date_month)
        select.select_by_value("7")  # Since July has a value of "7"

        start_date_day = driver.find_element(By.ID, "oleg_userdirectorybundle_siteparameters_academicYearStart_day")
        time.sleep(1)
        select = Select(start_date_day)
        select.select_by_value("1")
        time.sleep(3)

        start_date_year = driver.find_element(By.ID, "oleg_userdirectorybundle_siteparameters_academicYearStart_year")
        time.sleep(1)
        select = Select(start_date_year)
        select.select_by_value("2025")
        time.sleep(3)

        #oleg_userdirectorybundle_siteparameters_submit
        self.automation.click_button_by_id("oleg_userdirectorybundle_siteparameters_submit")
        print("academicYearStart populated")

        # Global academic end date
        url = "https://view.online/c/demo-institution/demo-department/directory/settings/1/edit?param=academicYearEnd"
        driver.get(url)
        time.sleep(3)

        end_date_month = driver.find_element(By.ID, "oleg_userdirectorybundle_siteparameters_academicYearEnd_month")
        time.sleep(1)
        select = Select(end_date_month)
        select.select_by_value("6")  # Since June has a value of "6"

        end_date_day = driver.find_element(By.ID, "oleg_userdirectorybundle_siteparameters_academicYearEnd_day")
        time.sleep(1)
        select = Select(end_date_day)
        select.select_by_value("30")
        time.sleep(3)

        end_date_year = driver.find_element(By.ID, "oleg_userdirectorybundle_siteparameters_academicYearEnd_year")
        time.sleep(1)
        select = Select(end_date_year)
        select.select_by_value("2025")
        time.sleep(3)

        # oleg_userdirectorybundle_siteparameters_submit
        self.automation.click_button_by_id("oleg_userdirectorybundle_siteparameters_submit")

        print("academicYearEnd populated")

        #Set UsernameType: Local User as default (set display order to -1)
        #Move 'Local User' to add as the first one in generateUsernameTypes()

        #For the demo site only, in Site Settings, change “Please use your CWID to log in.”
        # to “Institutional account integration is disabled on the Demo site.”
        url = "https://view.online/c/demo-institution/demo-department/directory/settings/1/edit?param=loginInstruction"
        driver.get(url)
        time.sleep(3)

        login_instruction = driver.find_element(By.ID, "oleg_userdirectorybundle_siteparameters_loginInstruction")
        login_instruction.clear()
        time.sleep(1)
        login_instruction_text = "Institutional account integration is disabled on the Demo site."
        login_instruction.send_keys(login_instruction_text)
        time.sleep(3)
        self.automation.click_button_by_id("oleg_userdirectorybundle_siteparameters_submit")
        time.sleep(1)

        print("loginInstruction changed")

    def remove_crons(self):
        driver = self.automation.get_driver()
        url = "https://view.online/c/demo-institution/demo-department/directory/admin/list/remove-cron-job/cron:importfellapp"
        driver.get(url)
        time.sleep(3)

        driver = self.automation.get_driver()
        url = "https://view.online/c/demo-institution/demo-department/directory/admin/list/remove-cron-job/cron:verifyimport"
        driver.get(url)
        time.sleep(3)

        driver = self.automation.get_driver()
        url = "https://view.online/c/demo-institution/demo-department/directory/admin/list/remove-cron-job/cron:invoice-reminder-emails"
        driver.get(url)
        time.sleep(3)

        driver = self.automation.get_driver()
        url = "https://view.online/c/demo-institution/demo-department/directory/admin/list/remove-cron-job/cron:expiration-reminder-emails"
        driver.get(url)
        time.sleep(3)

        driver = self.automation.get_driver()
        url = "https://view.online/c/demo-institution/demo-department/directory/admin/list/remove-cron-job/cron:project-sync"
        driver.get(url)
        time.sleep(3)

        driver = self.automation.get_driver()
        url = "https://view.online/c/demo-institution/demo-department/directory/admin/list/remove-cron-job/cron:status"
        driver.get(url)
        time.sleep(3)

        driver = self.automation.get_driver()
        url = "https://view.online/c/demo-institution/demo-department/directory/admin/list/remove-cron-job/webmonitor.py"
        driver.get(url)
        time.sleep(3)

    def run_deploy(self):
        driver = self.automation.get_driver()
        url = "https://view.online/c/demo-institution/demo-department/directory/admin/update-system-cache-assets/"
        driver.get(url)
        time.sleep(3)

    # @staticmethod
    # def run_deploy_command_old(self):
    #     subprocess.run(["/usr/bin/bash", "/srv/order-lab-tenantappdemo/orderflex/deploy.sh"], check=True)
    #     print("run_deploy_command: after deploy.sh")

    def run_deploy_command(self):
        subprocess.run(["/usr/bin/bash", "deploy.sh"], check=True, cwd="/srv/order-lab-tenantappdemo/orderflex")

    #NOT USED
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


def main(mailer_user,mailer_password,captcha_sitekey,captcha_secretkey):
    #run_by_symfony_command = True
    run_by_symfony_command = False
    automation = WebAutomation(run_by_symfony_command)
    automation.login_to_site()
    # Initialize using https://view.online/c/demo-institution/demo-department/directory/admin/first-time-login-generation-init/
    if mailer_user is None:
        mailer_user = "maileruser"
    if mailer_password is None:
        mailer_password = "mailerpassword"
    print("mailer_user:",mailer_user,", mailer_password:",mailer_password)
    init = Init(automation)
    init.initialize()
    init.run_site_settngs()
    init.init_other_settings()
    init.remove_crons()
    init.init_mailer(mailer_user,mailer_password)
    init.init_captcha(captcha_sitekey, captcha_secretkey)
    init.run_deploy()
    print("init done!")
    automation.quit_driver()

if __name__ == "__main__":
    #password = getpass.getpass("Enter your password: ")  # Secure input
    main(None,None, None, None)

#Test one function:
#python -c "import init.py; init.py.run_deploy_command()"
#python -c "import init; obj = init.Init(); obj.run_deploy_command()"

