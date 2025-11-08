from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
import time
import datetime
from selenium.webdriver.common.action_chains import ActionChains
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import re
import sys



#from scraper_base import login_to_site, initialize_driver
from web_automation import WebAutomation


class Users:
    def __init__(self, automation):
        self.automation = automation
        self.existing_users = {}
        #pass
    
    def get_users(self,with_admin=False):

        #Retrieves users with their details.

        raw_data = [
            #["Admin", "Admin", ""],
            ["Emily", "Parker", "Emily.Parker@example.com"],
            ["Sarah", "Mitchell", "Sarah.Mitchell@example.com"],
            ["Jessica", "Reed", "Jessica.Reed@example.com"],
            ["Megan", "Collins", "Megan.Collins@example.com"],
            ["Rachel", "Hayes", "Rachel.Hayes@example.com"],
            ["Amanda", "Foster", "Amanda.Foster@example.com"],
            ["Lauren", "Bennett", "Lauren.Bennett@example.com"],
            ["Natalie", "Brooks", "Natalie.Brooks@example.com"],
            ["Danielle", "Cooper", "Danielle.Cooper@example.com"],
            ["Olivia", "Grant", "Olivia.Grant@example.com"],
            # ["Hannah", "Peterson", "Hannah.Peterson@example.com"],
            # ["Nicole", "Morgan", "Nicole.Morgan@example.com"],
            # ["Stephanie", "Russell", "Stephanie.Russell@example.com"],
            # ["Madison", "Turner", "Madison.Turner@example.com"],
            # ["Allison", "Webb", "Allison.Webb@example.com"],
            # ["Victoria", "Simmons", "Victoria.Simmons@example.com"],
            # ["Chloe", "Jennings", "Chloe.Jennings@example.com"],
            # ["Brooke", "Sullivan", "Brooke.Sullivan@example.com"],
            # ["Michael", "Adams", "Michael.Adams@example.com"],
            # ["Christopher", "James", "Christopher.James@example.com"],
            # ["Matthew", "Harris", "Matthew.Harris@example.com"],
            # ["Joshua", "Ward", "Joshua.Ward@example.com"],
            # ["Andrew", "Price", "Andrew.Price@example.com"],
            # ["Ryan", "Phillips", "Ryan.Phillips@example.com"],
            # ["Jacob", "Howard", "Jacob.Howard@example.com"],
            # ["Justin", "Long", "Justin.Long@example.com"],
            # ["Daniel", "Carter", "Daniel.Carter@example.com"],
            # ["Nathan", "Scott", "Nathan.Scott@example.com"],
            # ["Alexander", "Reed", "Alexander.Reed@example.com"],
            # ["Benjamin", "Stone", "Benjamin.Stone@example.com"],
            # ["Samuel", "Morris", "Samuel.Morris@example.com"],
            # ["Jason", "Bell", "Jason.Bell@example.com"],
            # ["Eric", "Coleman", "Eric.Coleman@example.com"],
            # ["Kevin", "Rogers", "Kevin.Rogers@example.com"],
            # ["Tyler", "Murphy", "Tyler.Murphy@example.com"],
            # ["Brandon", "Hughes", "Brandon.Hughes@example.com"],
            # ["Taylor", "Morgan", "Taylor.Morgan@example.com"],
            # ["Jordan", "Lee", "Jordan.Lee@example.com"],
            # ["Casey", "Allen", "Casey.Allen@example.com"]
        ]

        if with_admin:
            raw_data.append(["Admin", "Admin", ""])

        #Returns:
        #    list: A list of user dictionaries containing user details.
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

        if 1:
            for first, last, email in raw_data:
                users.append({
                    'userid': (first + last).lower(),
                    'firstName': first,
                    'lastName': last,
                    'displayName': f"{first} {last}",
                    'email': email,
                    'password': 'pass',
                    'roles': ['ROLE_USERDIRECTORY_OBSERVER'],
                    'rolesStr': 'EmployeeDirectory Observer'
                })

        return users

    def get_existing_users(self,with_admin=False):
        automation = self.automation
        driver = automation.get_driver()
        driver.get(self.automation.baseurl.rstrip('/') + '/' + 'directory/users'.lstrip('/'))
        time.sleep(1)
        for user in self.get_users(with_admin):
            #time.sleep(1)
            #user_link = driver.find_element(By.XPATH, "//td/a[contains(text(), 'John Doe')]")
            #user_link = driver.find_element(By.XPATH, "//td/a[contains(text(), '"+user['displayName']+"')]")
            #user_link = WebDriverWait(driver, 10).until(
            #    EC.presence_of_element_located((By.XPATH, "//td/a[contains(text(), '"+user['displayName']+"')]"))
            #)
            #user_link = driver.find_element(By.XPATH, "//td/a[contains(normalize-space(text()), 'John Doe')]")
            #user_link = driver.find_element(By.XPATH, "//a/strong[contains(text(), 'John Doe')]")
            #print("get_existing_users: searching for displayName", user['displayName'])
            user_link = WebDriverWait(driver, 20).until(
                EC.presence_of_element_located((By.XPATH, "//a[strong[contains(text(), '"+user['displayName']+"')]]"))
            )
            # attributes = driver.execute_script(
            #     'var items = {}; for (index = 0; index < arguments[0].attributes.length; ++index) { items[arguments[0].attributes[index].name] = arguments[0].attributes[index].value }; return items;',
            #     user_link)
            # print("Attributes:", attributes)
            # Optionally, print or interact with the row
            #print("user_link:",user_link.text)
            #print("user_link.href:", user_link.href)
            #id_from_link = re.search(r'/user/(\d+)', user_link.text).group(1)
            href = user_link.get_attribute('href')
            id_from_link = href.split('/')[-1]  # Assumes the ID is the last part of the URL
            #print(f"Extracted ID: {id_from_link}")
            self.existing_users[user['displayName']] = id_from_link

        return self.existing_users

    def get_existing_user(self, display_name):
        automation = self.automation
        # automation.set_driver(driver)
        driver = automation.get_driver()
        driver.get(self.automation.baseurl.rstrip('/') + '/' + 'directory/users'.lstrip('/'))
        john_doe_row = driver.find_element(By.XPATH, "//td/a[contains(text(), 'John Doe')]")
        # Optionally, print or interact with the row
        #print(john_doe_row.text)

        id_from_link = re.search(r'/user/(\d+)', john_doe_row.text).group(1)
        #print(f"Extracted ID: {id_from_link}")

        return id_from_link

    def create_user(self):
        url = self.automation.baseurl.rstrip('/') + '/' + "directory/user/new".lstrip('/')
        #automation = WebAutomation()
        automation = self.automation
        #automation.set_driver(driver)
        driver = automation.get_driver()
        
        for user in self.get_users():
            print("Create user:", user['displayName'])
            driver.get(url)
            time.sleep(1)
            
            #$client->executeScript("$('#s2id_oleg_userdirectorybundle_user_keytype').select2('val','4')");
            #$("#select").select2("val", $("#select option:contains('Text')").val() );
            #automation.select_option("s2id_oleg_userdirectorybundle_user_keytype", "ID", "s2id_autogen2_search", "Local User")
            #time.sleep(3)
            #Triger switch so fields passwords are shown
            automation.select_option("s2id_oleg_userdirectorybundle_user_keytype", "ID", "s2id_autogen2_search",
                                     "Active Directory (LDAP)")
            time.sleep(3)
            automation.select_option("s2id_oleg_userdirectorybundle_user_keytype", "ID", "s2id_autogen2_search",
                                     "Local User")
            time.sleep(3)
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
            automation.select_option("s2id_oleg_userdirectorybundle_user_roles", "ID", "s2id_autogen4", user['rolesStr'])
            #automation.select_option("s2id_oleg_userdirectorybundle_user_roles", "s2id_autogen4", None, "EmployeeDirectory Observer")
            automation.select_option("s2id_oleg_userdirectorybundle_user_roles", "ID", "s2id_autogen4", user['rolesStr'])

            print("create_user: before click button")
            automation.click_button("btn-success")
            
            time.sleep(3)

            print("User created:", user['displayName'])

            #break
        
        #automation.quit_driver()

    def check_users(self):
        self.existing_users = self.get_existing_users()
        if len(self.existing_users) > 1:
            pass
        else:
            raise ValueError(f"Exit: users have not been created. Number of users {len(self.existing_users)}")
            #sys.exit("Exit: users have not been created.")

def main():
    
    # url = "https://view.online/c/demo-institution/demo-department/directory/login"
    # username_text = "administrator"
    # password_text = "1234567890"
    #
    # automation = WebAutomation()
    # automation.login_to_site(url, username_text, password_text)
    #
    # #Create user
    # users = Users(automation)
    # users.create_user()
    # users.check_users()
    # print("users done!")
    #
    # automation.quit_driver()
    run_by_symfony_command = False
    baseurl = "https://view.online/c/demo-institution/demo-department"
    automation = WebAutomation(baseurl, run_by_symfony_command)
    automation.login_to_site()
    users = Users(automation)
    users.create_user()
    users.check_users()
    time.sleep(3)
    automation.quit_driver()


# Execute the main function
if __name__ == "__main__":
    main()
