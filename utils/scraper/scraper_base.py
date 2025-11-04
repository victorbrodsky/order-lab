from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
import time
import datetime
from selenium.webdriver.common.action_chains import ActionChains
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC


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
    
    actions = ActionChains(driver)
    actions.move_to_element(combobox).click().perform()
    
    #combobox.click()

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
    # Set implicit wait
    driver.implicitly_wait(10)  # seconds
    
    baseurl = "https://view.online/c/demo-institution/demo-department"
    url = baseurl.rstrip('/') + '/' + "time-away-request/login".lstrip('/')
    
    username_text = "administrator"
    password_text = "1234567890"
    
    try:
        login_to_site(driver, url, username_text, password_text)
        #time.sleep(1)  # Wait for the page to load
        
        select_option(driver, "s2id_usernametypeid_show", "select2-input", "Local User")
        #time.sleep(1)
        
        click_button(driver, "btn-primary")
        #time.sleep(1)  # Observe the result before quitting
        
        #Create a new vacation request
        url = baseurl.rstrip('/') + '/' + "time-away-request/".lstrip('/')
        driver.get(url)
        
        #select_option(driver, "s2id_oleg_vacreqbundle_request_institution", "select2-input", "Pathology and Laboratory Medicine ID#29 (for review by administrator)")
        
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

        script = """
            var selectElement = document.getElementById('oleg_vacreqbundle_request_institution');
            selectElement.value = '29';  // Corresponds to "Pathology and Laboratory Medicine"
            var event = new Event('change', { bubbles: true });
            selectElement.dispatchEvent(event);
            """
        driver.execute_script(script)
        time.sleep(5)

        #combobox = driver.find_element(By.ID, "s2id_oleg_vacreqbundle_request_institution")
        #combobox.click()
        #search_box = driver.find_element(By.CLASS_NAME, "select2-choice")
        #search_box.send_keys("Pathology and Laboratory Medicine ID#29 (for review by administrator)")
        #search_box.send_keys(Keys.ENTER)
        
#         time.sleep(3)
#         
#         # Set implicit wait
#         driver.implicitly_wait(10)  # seconds
#         
        #select_option(driver, "s2id_oleg_vacreqbundle_request_user", "select2-choice", "John Doe - johndoe (Local User)")
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

        script = """
            var selectElement = document.querySelector('#oleg_vacreqbundle_request_user');
            selectElement.value = '12';  // Corresponds to "John Doe"
            var event = new Event('change', { bubbles: true });
            selectElement.dispatchEvent(event);
            """
        driver.execute_script(script)
        time.sleep(5)

        #Select start date
        # Calculate the date for 1 week ago
        one_week_ago = (datetime.date.today() - datetime.timedelta(days=7)).strftime("%m-%d-%Y") #"%Y-%m-%d"
        #print("one_week_ago=",one_week_ago)
        # Find the datepicker input field
        datepicker = driver.find_element(By.ID, "oleg_vacreqbundle_request_requestVacation_startDate")
        # Clear the field and enter the calculated date
        datepicker.clear()
        datepicker.send_keys(one_week_ago)
        
        time.sleep(5)
        
        datepicker = driver.find_element(By.ID, "oleg_vacreqbundle_request_requestVacation_endDate")
        # Clear the field and enter the calculated date
        datepicker.clear()
        datepicker.send_keys(one_week_ago)
        time.sleep(5)
        
        number_of_days_field = driver.find_element(By.ID, "oleg_vacreqbundle_request_requestVacation_numberOfDays")
        #number_of_days_field.click()
        driver.execute_script("arguments[0].scrollIntoView(true);", number_of_days_field)
        number_of_days_field.clear()
        #number_of_days_field.send_keys('1')
        driver.execute_script("arguments[0].value = arguments[1];", number_of_days_field, "1")
        #days.click();
        time.sleep(5)

        #calculate_button = driver.find_element(By.CLASS_NAME, "calculate-btn")
        # Click the "Calculate" button
        #calculate_button.click()

        # Optional: Wait to observe the action
        #time.sleep(3)

        button = driver.find_element(By.ID, "btnCreateVacReq")
        button.click()
        
        time.sleep(5)
        
        
    finally:
        driver.quit()

# Execute the main function
if __name__ == "__main__":
    main()