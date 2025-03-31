from web_automation import WebAutomation
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from users import Users
import time

class Trp:
    def __init__(self, automation):
        self.automation = automation
        self.users = Users(automation)

    def create_project(self):
        driver = self.automation.get_driver()
        url = "https://view.online/c/demo-institution/demo-department/translational-research/project/new/ap-cp?requester-group=Internal"
        driver.get(url)
        time.sleep(1)

        users = self.users.get_users()
        pi = users[0]

        self.automation.select_option("s2id_oleg_translationalresearchbundle_project_principalInvestigators", "CSS_SELECTOR",
                                      ".select2-search-field .select2-input",
                                      # "John Doe"
                                      pi['displayName']
                                      )
        time.sleep(3)
        #TODO: click cancel on add new user modal
        #self.automation.click_button_by_id("user-add-btn-cancel")
        #cancel_button = driver.find_element(By.ID, "user-add-btn-cancel")
        #cancel_button.click()
        #cancel_button = WebDriverWait(driver, 10).until(
        #    EC.element_to_be_clickable((By.ID, "user-add-btn-cancel"))
        #)
        # Click the button
        #cancel_button.click()

        # Wait for the modal dialog to be visible
        # modal = WebDriverWait(driver, 10).until(
        #     EC.visibility_of_element_located((By.CLASS_NAME, "modal-dialog"))
        # )
        # # Now locate and click the "Cancel" button
        # cancel_button = modal.find_element(By.ID, "user-add-btn-cancel")
        # cancel_button.click()

        # modal_dialog = driver.find_element(By.CLASS_NAME, "modal-dialog")
        # # Print or interact with the modal
        # print("Modal dialog found:", modal_dialog)
        # time.sleep(3)
        # #cancel_button = modal_dialog.find_element(By.ID, "user-add-btn-cancel")
        # cancel_button = modal_dialog.find_element(By.CLASS_NAME,"btn btn-primary")
        # # Click the "Cancel" button
        # time.sleep(3)
        # cancel_button.click()
        # script = """
        #     var selectElement = document.getElementById('oleg_vacreqbundle_request_institution');
        #     selectElement.value = '29';  // Corresponds to "Pathology and Laboratory Medicine"
        #     var event = new Event('change', { bubbles: true });
        #     selectElement.dispatchEvent(event);
        #     """
        # script = """
        #     var cancelButton = $("#user-add-btn-cancel");
        #     cancelButton.click(function() {
        #         alert("Cancel button clicked!");
        #     });
        # """
        script = """
            $("#user-add-btn-cancel").trigger("click");
        """
        driver.execute_script(script)

        time.sleep(3)

        billing_contact = users[1]
        self.automation.select_option("s2id_oleg_translationalresearchbundle_project_billingContact",
                                      "CSS_SELECTOR",
                                      ".select2-search .select2-input",
                                      # "John Doe"
                                      billing_contact['displayName']
                                      )
        self.automation.click_button_by_id("user-add-btn-cancel")
        time.sleep(3)

def main():
    url = "https://view.online/c/demo-institution/demo-department/directory/login"
    username_text = "administrator"
    password_text = "1234567890_demo"
    automation = WebAutomation()
    automation.login_to_site(url, username_text, password_text)

    trp = Trp(automation)
    trp.create_project()

    automation.quit_driver()

if __name__ == "__main__":
    main()


