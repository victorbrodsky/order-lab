from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.action_chains import ActionChains
from selenium.common.exceptions import NoSuchElementException
from selenium.common.exceptions import TimeoutException
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
#import logging
import sys
import time

#Note: enable Swap memory:
#Check current swap: sudo swapon --show
#Allocate 2GB for swap: sudo fallocate -l 2G /swapfile
#Set Permissions: sudo chmod 600 /swapfile
#Format as Swap: sudo mkswap /swapfile
#Enable Swap: sudo swapon /swapfile
#Make Swap Permanent. Add this line to /etc/fstab: /swapfile none swap sw 0 0

#Error: Message: session not created: Chrome instance exited. Examine ChromeDriver verbose log to determine the cause.
#Install google-chrome:
#wget https://dl.google.com/linux/direct/google-chrome-stable_current_x86_64.rpm
#sudo dnf localinstall ./google-chrome-stable_current_x86_64.rpm
#google-chrome --version
#Remove downloaded RPM file: rm google-chrome-stable_current_x86_64.rpm
#
#wget https://storage.googleapis.com/chrome-for-testing-public/142.0.7444.61/linux64/chromedriver-linux64.zip
#unzip chromedriver-linux64.zip
#sudo mv chromedriver-linux64/chromedriver /usr/local/bin/chromedriver
#chmod +x /usr/local/bin/chromedriver
#chromedriver --version



class WebAutomation:
    def __init__(self, baseurl, run_by_symfony_command=False):
        """Initialize the class and set up the WebDriver."""
        self.driver = None
        self.baseurl = baseurl
        self.run_by_symfony_command = run_by_symfony_command
        self.initialize_driver()
        print(f"__init__: self. self.baseurl={self.baseurl}, run_by_symfony_command={self.run_by_symfony_command}")

    def get_driver(self):
        return self.driver

    #Error: selenium.common.exceptions.SessionNotCreatedException:
    # Message: session not created: probably user data directory is already in use, please specify a unique value for
    # --user-data-dir argument, or don't use --user-data-dir
    #Stops all running Chrome processes on Linux: pkill -f chrome
    def initialize_driver(self):
        """Initializes the WebDriver."""
        options = webdriver.ChromeOptions()

        #run_by_symfony_command is True if calling by Symfony command in php
        #run_by_symfony_command = False
        #run_by_symfony_command = True

        if self.run_by_symfony_command is True:
            print("initialize_driver with --headless")
            options.add_argument("--headless")  # working in command. Run a virtual browser without a graphical user interface
            #options.add_argument("--headless=new")  #testing: Use headless mode if on a server
        else:
            print("initialize_driver: run by console or pycharm")
            options.add_experimental_option("detach", True)  # keep browser open

        options.add_argument("--no-sandbox") #working in command. Disable the Chrome sandbox, which is a security feature that isolates browser processes
        options.add_argument("--disable-dev-shm-usage") #working in command. Prevent Chrome from using shared memory

        #options.add_argument("--incognito")  # Example: Run the browser in incognito mode
        #options.add_argument("--disable-extensions")  # Disable browser extensions
        # options.add_argument("--user-data-dir=/usr/local/bin/order-lab-tenantappdemo/orderflex/var/log/")  # Replace this with a valid, unique path
        # options.add_argument("--user-data-dir=/tmp/chrome-user-data")  # Use a unique directory

        # service = Service(
        #     #executable_path="/usr/bin/chromedriver",
        #     log_path="/var/log/selenium/chromedriver.log",  # Save log to file
        #     verbose=True
        # )
        #service = Service()
        #service.enable_verbose_logging = True
        #service.log_path = '/var/log/selenium/chromedriver.log'

        self.driver = webdriver.Chrome(options=options)
        #self.driver = webdriver.Chrome(service=service, options=options) #testing

        #Testing
        #self.driver.get("https://www.google.com")
        #print(self.driver.title)
        #self.driver.quit()
        #exit()

        self.driver.set_page_load_timeout(120) # Increase timeout to handle delays
        return self.driver

    def login_to_site(self, url=None, username_text=None, password_text=None):

        if self.check_if_loggedin():
            return True

        wait = WebDriverWait(self.driver, 10)

        """Logs in to the site."""
        if url is None:
            #url = "https://view.online/c/demo-institution/demo-department/directory/login"
            url = self.baseurl.rstrip('/') + '/' + "directory/login".lstrip('/')
        print("login_to_site: url=",url)

        if username_text is None:
            username_text = "administrator"

        if password_text is None:
            password_text = "1234567890"

        if url is not None:
            print("use login url ",url)
            self.driver.get(url)

        time.sleep(1)

        # Wait for username and password fields to be present
        username = wait.until(EC.presence_of_element_located((By.ID, "display-username")))
        password = wait.until(EC.presence_of_element_located((By.ID, "password")))

        # Send credentials
        username.send_keys(username_text)
        password.send_keys(password_text)
        time.sleep(3)

        # Wait for Select2 input to be visible and interactable
        select2_input = wait.until(EC.visibility_of_element_located((By.ID, "s2id_usernametypeid_show")))

        # Select the desired option
        self.select_option("s2id_usernametypeid_show", "CLASS_NAME", "select2-input", "Local User")

        self.click_button("btn-primary")
        time.sleep(1)
        self.driver.save_screenshot("after_login_to_site.png")

    def check_if_loggedin(self):
        try:
            url = self.baseurl.rstrip('/') + '/directory'
            self.driver.get(url)

            try:
                # Wait up to 10 seconds for the login form to appear
                WebDriverWait(self.driver, 10).until(
                    EC.presence_of_element_located((By.ID, "login-form"))
                )
                print("Login form is present. User is NOT logged in.")
                return False
            except TimeoutException:
                print("Login form not found. User is likely logged in.")
                return True

        except Exception as e:
            print(f"Unexpected error: {e}")
            return False

    def check_login_page(self):
        #Check if system is login able
        #url = "https://view.online/c/demo-institution/demo-department/directory/logout"
        url = self.baseurl.rstrip('/') + '/' + "directory/logout".lstrip('/')
        self.driver.get(url)
        time.sleep(3)

        #url = "https://view.online/c/demo-institution/demo-department/directory/login"
        url = self.baseurl.rstrip('/') + '/' + "directory/login".lstrip('/')
        self.driver.get(url)
        time.sleep(3)

        try:
            element = self.driver.find_element(By.ID, "display-username")
            print("check_login_page: Element display-username found!")
            return True
        except NoSuchElementException:
            print("check_login_page: Element display-username not found.")
            self.driver.save_screenshot("login_page_error.png")
            #sys.exit()
            return False

    # def select_option(self, element_id, selector_option, selector_text, option_text):
    #     #print("ID=",select_id,", CLASS=", select_classname)
    #     #"""Selects an option from the Select2 combobox."""
    #     combobox = self.driver.find_element(By.ID, element_id)
    #     actions = ActionChains(self.driver)
    #     actions.move_to_element(combobox).click().perform()
    #
    #     time.sleep(1)
    #
    #     if selector_option == "ID":
    #         search_box = self.driver.find_element(By.ID, selector_text)
    #         #print("search by ID=",selector_text)
    #     if selector_option == "CLASS_NAME":
    #         search_box = self.driver.find_element(By.CLASS_NAME, selector_text)
    #         #print("search by CLASS_NAME=",selector_text)
    #     if selector_option == "CSS_SELECTOR":
    #         search_box = self.driver.find_element(By.CSS_SELECTOR, selector_text)
    #         #print("search by CSS_SELECTOR=",selector_text)
    #
    #     time.sleep(1)
    #     search_box.send_keys(option_text)
    #     time.sleep(1)
    #     search_box.send_keys(Keys.ENTER)
    #     #time.sleep(3)

    def select_option(self, element_id, selector_option, selector_text, option_text):
        """Selects an option from the Select2 combobox."""
        wait = WebDriverWait(self.driver, 10)

        # Wait for and click the combobox
        combobox = wait.until(EC.element_to_be_clickable((By.ID, element_id)))
        ActionChains(self.driver).move_to_element(combobox).click().perform()

        # Wait for the search box based on selector type
        if selector_option == "ID":
            search_box = wait.until(EC.visibility_of_element_located((By.ID, selector_text)))
        elif selector_option == "CLASS_NAME":
            search_box = wait.until(EC.visibility_of_element_located((By.CLASS_NAME, selector_text)))
        elif selector_option == "CSS_SELECTOR":
            search_box = wait.until(EC.visibility_of_element_located((By.CSS_SELECTOR, selector_text)))
        else:
            raise ValueError(f"Unsupported selector_option: {selector_option}")

        # Send the option text and confirm selection
        search_box.send_keys(option_text)
        wait.until(lambda d: option_text.lower() in search_box.get_attribute("value").lower())
        search_box.send_keys(Keys.ENTER)

    def click_button(self, class_name):
        """Clicks a button with the specified class name."""
        button = self.driver.find_element(By.CLASS_NAME, class_name)
        button.click()

    def click_button_by_id(self, element_id):
        """Clicks a button with the specified class name."""
        button = self.driver.find_element(By.ID, element_id)
        button.click()

    def set_driver(self,driver):
        self.driver = driver

    def quit_driver(self):
        """Quits the WebDriver."""
        if self.driver:
            #print("quit_driver: before cloes")
            #self.driver.close()
            print("quit_driver: before quit")
            self.driver.quit()

# Usage Example:
if __name__ == "__main__":
    baseurl = "http://localhost/".rstrip('/')
    automation = WebAutomation(baseurl, False)
    driver = automation.initialize_driver()
    # You can now call methods like:
    # automation.login_to_site("https://example.com", "your_username", "your_password")
    # automation.select_option("element_id", "select_classname", "option_text")
    # automation.click_button("button_class_name")
    automation.quit_driver()
