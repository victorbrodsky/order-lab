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

    def get_trp_projects(self):
        projects = [
            {
                'title': 'Inflammatory infiltrates in Post-transplant lymphoproliferative disorders (PTLDs)',
                'description': ('Post-transplant lymphoproliferative disorders (PTLDs) are Epstein Barr virus (EBV) '
                                'associated B cell lymphoid proliferations. The patients who develop these lesions '
                                'have an unpredictable clinical course and outcome with some patients having lesions '
                                'that regress following a reduction in immunosuppression and others who despite '
                                'aggressive therapeutic intervention have progressive disease leading to their demise.'),
                'budget': '5000',
                'funded': 1
            },
            {
                'title': 'Characterization of circulating tumor cells in arterial vs. venous blood of patients with Non Small Cell Lung Cancer',
                'description': (
                    'This is a phase I study to determine whether the incidence and quantity of circulating '
                    'tumor cells is higher in peripheral arterial compared to venous blood and of the primary '
                    'tumor. A total of 50 evaluable subjects will be enrolled from 4 cancer centers with early '
                    'resectable NSCLC and subjects with unresectable or metastatic disease will be enrolled.'),
                'budget': '10000',
                'funded': 1
            },
            {
                'title': 'Assess types of stroma response in fibrogenic myeloid neoplasms',
                'description': ('Our goal is to assess types of stroma response in fibrogenic myeloid neoplasms, '
                                'particularly mastocytosis and CIMF. Altered stroma microenvironment is a common feature '
                                'of many tumors. There is increasing evidence that these stromal changes, including '
                                'increased proteases and cytokines, may promote tumor progression.'),
                'budget': '3000',
                'funded': 1
            }
        ]
        return projects

    def create_project(self):
        for project in self.get_trp_projects():
            self.create_single_project(project)
            break

    def create_single_project(self, project):
        driver = self.automation.get_driver()
        url = "https://view.online/c/demo-institution/demo-department/translational-research/project/new/ap-cp?requester-group=Internal"
        driver.get(url)
        time.sleep(1)

        users = self.users.get_users()
        pi = users[0]

        # self.automation.select_option("s2id_oleg_translationalresearchbundle_project_principalInvestigators", "CSS_SELECTOR",
        #                               ".select2-search-field .select2-input",
        #                               # "John Doe"
        #                               pi['displayName']
        #                               )

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
        # $client->executeScript(
        #     "$('#s2id_oleg_translationalresearchbundle_project_principalInvestigators').select2('val','".$piArr['userId']."')");
        user_id = self.users.get_existing_user('John Doe')
        print(f"User ID: {user_id}")
        time.sleep(3)

        script = """
            $("#s2id_oleg_translationalresearchbundle_project_principalInvestigators").select2('val','12');
        """
        driver.execute_script(script)

        time.sleep(3)

        # billing_contact = users[1]
        # self.automation.select_option("s2id_oleg_translationalresearchbundle_project_billingContact",
        #                               "CSS_SELECTOR",
        #                               ".select2-search .select2-input",
        #                               # "John Doe"
        #                               billing_contact['displayName']
        #                               )
        # self.automation.click_button_by_id("user-add-btn-cancel")

        script = """
                    $("#s2id_oleg_translationalresearchbundle_project_billingContact").select2('val','15');
                """
        driver.execute_script(script)

        time.sleep(3)
        # self.automation.select_option("s2id_oleg_translationalresearchbundle_project_exemptIACUCApproval",
        #                               "CSS_SELECTOR",
        #                               ".select2-search .select2-input",
        #                               "Exempt"
        #                               )
        script = """
                    $("#oleg_translationalresearchbundle_project_exemptIrbApproval").select2('val','2');
                """
        driver.execute_script(script)

        time.sleep(3)
        title = driver.find_element(By.ID, "oleg_translationalresearchbundle_project_title")
        title.send_keys(project['title'])

        #human tissue
        no_radio_button = driver.find_element(By.ID, 'oleg_translationalresearchbundle_project_involveHumanTissue_1')
        no_radio_button.click()
        # Optionally, verify if the "No" radio button is selected
        assert no_radio_button.is_selected()

        time.sleep(3)
        title = driver.find_element(By.ID, "oleg_translationalresearchbundle_project_description")
        title.send_keys(project['description'])

        title = driver.find_element(By.ID, "oleg_translationalresearchbundle_project_totalCost")
        title.send_keys(project['budget'])

        no_radio_button = driver.find_element(By.ID, 'oleg_translationalresearchbundle_project_requireTissueProcessing_1')
        no_radio_button.click()
        # Optionally, verify if the "No" radio button is selected
        assert no_radio_button.is_selected()

        no_radio_button = driver.find_element(By.ID,'oleg_translationalresearchbundle_project_requireArchivalProcessing_1')
        no_radio_button.click()
        # Optionally, verify if the "No" radio button is selected
        assert no_radio_button.is_selected()

        time.sleep(3)

        #self.automation.click_button_by_id("oleg_translationalresearchbundle_project_submitIrbReview")
        time.sleep(10)

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


