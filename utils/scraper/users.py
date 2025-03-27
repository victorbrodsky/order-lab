from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
import time
import datetime
from selenium.webdriver.common.action_chains import ActionChains
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

#from scraper_base import login_to_site, initialize_driver
from web_automation import WebAutomation


class Users:
    def __init__(self):
        #self.automation = WebAutomation()
        pass
    
    def get_users(self):
        """
        Retrieves users with their details.
        
        Returns:
            list: A list of user dictionaries containing user details.
        """
        users = [
            {
                'userid': 'johndoe',
                'firstName': 'John',
                'lastName': 'Doe',
                'displayName': 'John Doe',
                'email': 'cinava@yahoo.com',
                'password': 'pass',
                'roles': ['ROLE_USERDIRECTORY_OBSERVER'],
                'rolesStr': 'EmployeeDirectory Observer',
                #'userId': 12
            },
            {
                'userid': 'aeinstein',
                'firstName': 'Albert',
                'lastName': 'Einstein',
                'displayName': 'Albert Einstein',
                'email': 'cinava@yahoo.com',
                'password': 'pass',
                'roles': ['ROLE_USERDIRECTORY_OBSERVER'],
                'rolesStr': 'EmployeeDirectory Observer',
                #'userId': 15
            },
            {
                'userid': 'rrutherford',
                'firstName': 'Ernest',
                'lastName': 'Rutherford',
                'displayName': 'Ernest Rutherford',
                'email': 'cinava@yahoo.com',
                'password': 'pass',
                'roles': ['ROLE_USERDIRECTORY_OBSERVER'],
                'rolesStr': 'EmployeeDirectory Observer',
                #'userId': 16
            }
        ]
        return users

    def create_user(self, driver):
        url = "https://view.online/c/demo-institution/demo-department/directory/user/new"
        automation = WebAutomation()
        automation.set_driver(driver)
        
        for user in self.get_users():
            print(user['userid'])
            driver.get(url)
            time.sleep(3)
            
            #$client->executeScript("$('#s2id_oleg_userdirectorybundle_user_keytype').select2('val','4')");
            #$("#select").select2("val", $("#select option:contains('Text')").val() );
            automation.select_option("s2id_oleg_userdirectorybundle_user_keytype", "s2id_autogen2_search", None, "Local User")
            #primaryPublicUserId = driver.find_element(By.ID, "oleg_userdirectorybundle_user_primaryPublicUserId")
            #primaryPublicUserId.send_keys(user['userid'])
            
#             combobox = driver.find_element(By.ID, "s2id_oleg_userdirectorybundle_user_keytype")
#             actions = ActionChains(driver)
#             actions.move_to_element(combobox).click().perform()
#             time.sleep(3)           
#             search_box = driver.find_element(By.ID, "s2id_autogen2_search")
#             #search_box = driver.find_element(By.CLASS_NAME, "select2-input")            
#             time.sleep(3)          
#             search_box.send_keys("Local User")          
#             time.sleep(3)           
#             search_box.send_keys(Keys.ENTER)
            
            field = driver.find_element(By.ID, "oleg_userdirectorybundle_user_primaryPublicUserId")
            field.send_keys(user['userid'])
            
            field = driver.find_element(By.ID, "oleg_userdirectorybundle_user_infos_0_displayName")
            field.send_keys(user['displayName'])
            
            field = driver.find_element(By.ID, "oleg_userdirectorybundle_user_infos_0_firstName")
            field.send_keys(user['firstName'])
            
            field = driver.find_element(By.ID, "oleg_userdirectorybundle_user_infos_0_lastName")
            field.send_keys(user['lastName'])
            
            field = driver.find_element(By.ID, "oleg_userdirectorybundle_user_infos_0_email")
            field.send_keys(user['email'])
            
            field = driver.find_element(By.ID, "oleg_userdirectorybundle_user_password_first")
            field.clear()
            field.send_keys(user['password'])
            
            field = driver.find_element(By.ID, "oleg_userdirectorybundle_user_password_second")
            field.clear()
            field.send_keys(user['password'])
            
            #$client->executeScript("$('#oleg_userdirectorybundle_user_roles').select2('val',[".$roleStr."])");
            automation.select_option("s2id_oleg_userdirectorybundle_user_roles", "s2id_autogen4", None, user['rolesStr'])
            #automation.select_option("s2id_oleg_userdirectorybundle_user_roles", "s2id_autogen4", None, "EmployeeDirectory Observer")
            automation.select_option("s2id_oleg_userdirectorybundle_user_roles", "s2id_autogen4", None, user['rolesStr'])
                
            automation.click_button("btn-success")
            
            time.sleep(10)
            
            break
        
        automation.quit_driver()

def main():
    
    url = "https://view.online/c/demo-institution/demo-department/directory/login"
    username_text = "administrator"
    password_text = "1234567890_demo"
    
    automation = WebAutomation()
    driver = automation.initialize_driver()
    # You can now call methods like:
    automation.login_to_site(url, username_text, password_text)
    # automation.select_option("element_id", "select_classname", "option_text")
    # automation.click_button("button_class_name")    
    
    #Create user
    users = Users()
    users.create_user(driver)


# Execute the main function
if __name__ == "__main__":
    main()
