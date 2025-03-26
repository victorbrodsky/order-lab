from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
import time

def initialize_driver():
    """Initializes the WebDriver."""
    options = webdriver.ChromeOptions()
    options.add_experimental_option("detach", True)
    driver = webdriver.Chrome(options=options)
    return driver

def login_to_site(driver, url, username_text, password_text):
    """Logs in to the site."""
    driver.get(url)
    
    # Find username and password fields and enter the credentials
    username = driver.find_element(By.ID, "display-username")
    password = driver.find_element(By.ID, "password")
    username.send_keys(username_text)
    password.send_keys(password_text)

def select_option(driver, element_id, select_classname, option_text):
    """Selects an option from the Select2 combobox."""
    combobox = driver.find_element(By.ID, element_id)
    combobox.click()

    search_box = driver.find_element(By.CLASS_NAME, select_classname)
    search_box.send_keys(option_text)
    search_box.send_keys(Keys.ENTER)

def click_button(driver, className):
    """Clicks the Log In button."""
    button = driver.find_element(By.CLASS_NAME, className)
    button.click()

def main():
    """Main function to execute all actions."""
    driver = initialize_driver()
    url = "https://view.online/c/demo-institution/demo-department/time-away-request/login"
    username_text = "administrator"
    password_text = "1234567890_demo"
    
    try:
        login_to_site(driver, url, username_text, password_text)
        time.sleep(2)  # Wait for the page to load
        
        select_option(driver, "s2id_usernametypeid_show", "select2-input", "Local User")
        time.sleep(2)
        
        click_button(driver, "btn-primary")
        time.sleep(3)  # Observe the result before quitting
        
        #Create a new vacation request
        url = "https://view.online/c/demo-institution/demo-department/time-away-request/"
        driver.get(url)
        select_option(driver, "s2id_oleg_vacreqbundle_request_institution", "select2-choice", "Pathology and Laboratory Medicine ID#29 (for review by administrator)")
        
        #combobox = driver.find_element(By.ID, "s2id_oleg_vacreqbundle_request_institution")
        #combobox.click()
        #search_box = driver.find_element(By.CLASS_NAME, "select2-choice")
        #search_box.send_keys("Pathology and Laboratory Medicine ID#29 (for review by administrator)")
        #search_box.send_keys(Keys.ENTER)
        
        select_option(driver, "s2id_oleg_vacreqbundle_request_user", "select2-choice", "John Doe - johndoe (Local User)")
        time.sleep(10)  # Observe the result before quitting
        
    finally:
        driver.quit()

# Execute the main function
if __name__ == "__main__":
    main()