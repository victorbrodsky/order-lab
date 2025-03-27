from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.action_chains import ActionChains
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

class WebAutomation:
    def __init__(self):
        """Initialize the class and set up the WebDriver."""
        self.driver = None

    def initialize_driver(self):
        """Initializes the WebDriver."""
        options = webdriver.ChromeOptions()
        options.add_experimental_option("detach", True)
        self.driver = webdriver.Chrome(options=options)
        return self.driver

    def login_to_site(self, url, username_text, password_text):
        """Logs in to the site."""
        self.driver.get(url)
        username = self.driver.find_element(By.ID, "display-username")
        password = self.driver.find_element(By.ID, "password")
        username.send_keys(username_text)
        password.send_keys(password_text)

    def select_option(self, element_id, select_classname, option_text):
        """Selects an option from the Select2 combobox."""
        combobox = self.driver.find_element(By.ID, element_id)
        actions = ActionChains(self.driver)
        actions.move_to_element(combobox).click().perform()

        search_box = self.driver.find_element(By.CLASS_NAME, select_classname)
        search_box.send_keys(option_text)
        search_box.send_keys(Keys.ENTER)

    def click_button(self, class_name):
        """Clicks a button with the specified class name."""
        button = self.driver.find_element(By.CLASS_NAME, class_name)
        button.click()

    def quit_driver(self):
        """Quits the WebDriver."""
        if self.driver:
            self.driver.quit()

# Usage Example:
if __name__ == "__main__":
    automation = WebAutomation()
    driver = automation.initialize_driver()
    # You can now call methods like:
    # automation.login_to_site("https://example.com", "your_username", "your_password")
    # automation.select_option("element_id", "select_classname", "option_text")
    # automation.click_button("button_class_name")
    automation.quit_driver()

