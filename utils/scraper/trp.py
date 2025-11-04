from web_automation import WebAutomation
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from users import Users
import time
from datetime import date
from dateutil.relativedelta import relativedelta


class Trp:
    def __init__(self, automation):
        self.automation = automation
        self.users = Users(automation)
        self.existing_users = self.users.get_existing_users()
        self.project_ids = []

    def set_automation(self, automation):
        self.automation = automation

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

    def get_trp_work_requests(self):
        requests = [
            {
                'serviceId': '1',
                'serviceName': 'TRP-1001',
                'quantity': '3',
                'comment': 'Request for RNA extraction. For each case below, annotated H&E slide is provided.',
            },
            {
                'serviceId': '2',
                'serviceName': 'TRP-1002',
                'quantity': '4',
                'comment': ('Test included in this Panel are:\n'
                            'Albumin, Alkaline Phosphatase, Total Bilirubin, Carbon Dioxide (CO2), Aspartate'),
            },
            {
                'serviceId': '3',
                'serviceName': 'TRP-1003',
                'quantity': '5',
                'comment': ('For case S12-257 A9, already in TRP:\n'
                            '1. Please cut 3 additional unstained slides 5-micron.\n'
                            '2. Please label each slide with Research ID only'),
            }
        ]
        return requests

    def create_projects(self):
        counter = 0
        for project in self.get_trp_projects():
            self.create_single_project(project,counter)
            counter = counter + 1
            #break

    def create_single_project(self, project, counter):
        driver = self.automation.get_driver()
        url = self.automation.baseurl.rstrip('/') + '/' + "translational-research/project/new/ap-cp?requester-group=Internal".lstrip('/')
        driver.get(url)
        time.sleep(1)

        users = self.users.get_users()
        pi = users[counter]

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

        #user_id = self.users.get_existing_user('John Doe')
        user_id = self.existing_users[pi['displayName']]
        #print(f"pi User ID: {user_id}")
        time.sleep(1)

        # script = """
        #     $("#s2id_oleg_translationalresearchbundle_project_principalInvestigators").select2('val','12');
        # """
        script = f"""
            $("#s2id_oleg_translationalresearchbundle_project_principalInvestigators").select2('val','{user_id}');
        """
        driver.execute_script(script)

        time.sleep(3)

        billing_contact = users[1]
        # self.automation.select_option("s2id_oleg_translationalresearchbundle_project_billingContact",
        #                               "CSS_SELECTOR",
        #                               ".select2-search .select2-input",
        #                               # "John Doe"
        #                               billing_contact['displayName']
        #                               )
        # self.automation.click_button_by_id("user-add-btn-cancel")

        user_id = self.existing_users[billing_contact['displayName']]
        #print(f"billing_contact User ID: {user_id}")
        time.sleep(1)
        # script = """
        #             $("#s2id_oleg_translationalresearchbundle_project_billingContact").select2('val','15');
        #         """
        script = f"""
                    $("#s2id_oleg_translationalresearchbundle_project_billingContact").select2('val','{user_id}');
                """
        driver.execute_script(script)

        time.sleep(3)
        # self.automation.select_option("s2id_oleg_translationalresearchbundle_project_exemptIACUCApproval",
        #                               "CSS_SELECTOR",
        #                               ".select2-search .select2-input",
        #                               "Exempt"
        #                               )
        # script = """
        #             $("#oleg_translationalresearchbundle_project_exemptIrbApproval").select2('val','2');
        #         """
        # driver.execute_script(script)
        irb_exp_date = driver.find_element(By.ID, "oleg_translationalresearchbundle_project_irbNumber")
        irb_exp_date.send_keys('2634793')

        #set exp date 1 year plus
        #one_year_plus = (datetime.date.today() + datetime.timedelta(year=1)).strftime("%m-%d-%Y")  # "%Y-%m-%d"
        today = date.today()
        # Add one year
        one_year_plus = today + relativedelta(years=1)
        #print("one_year_plus=", one_year_plus)
        one_year_plus_str = one_year_plus.strftime("%m-%d-%Y")
        #print("one_year_plus_str=", one_year_plus_str)
        # Find the datepicker input field
        datepicker = driver.find_element(By.ID, "oleg_translationalresearchbundle_project_irbExpirationDate")
        # Clear the field and enter the calculated date
        datepicker.clear()
        datepicker.send_keys(one_year_plus_str)

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
        time.sleep(3)
        no_radio_button.click()
        # Optionally, verify if the "No" radio button is selected
        assert no_radio_button.is_selected()

        no_radio_button = driver.find_element(By.ID,'oleg_translationalresearchbundle_project_requireArchivalProcessing_1')
        time.sleep(3)
        no_radio_button.click()
        # Optionally, verify if the "No" radio button is selected
        assert no_radio_button.is_selected()

        time.sleep(3)

        self.automation.click_button_by_id("oleg_translationalresearchbundle_project_submitIrbReview")
        time.sleep(3)

        #Approve the project
        current_url = driver.current_url
        print(f"Current URL: {current_url}")
        project_id = current_url.split('/')[-1]
        #print(f"Extracted Project ID: {project_id}")
        #driver.get('https://view.online/c/demo-institution/demo-department/translational-research/projects/')
        driver.get(self.automation.baseurl.rstrip('/') + '/' + f"translational-research/approve-project/{project_id}".lstrip('/'))
        print(f"Approved Project ID: {project_id}")
        self.project_ids.append(project_id)
        time.sleep(3)

        #Create 3 work requests for this project
        #self.create_work_requests(project_id)

    def create_work_requests(self):
        print("Project IDs:", self.project_ids)  # Debugging step
        print("Number of work requests:", len(self.get_trp_work_requests()))
        print("Number of project IDs:", len(self.project_ids))
        for project_id in self.project_ids:
            self.create_work_requests_by_project(project_id)

        # i = 0
        # for work_requests in self.get_trp_work_requests():
        #     project_id = self.project_ids[i]
        #     self.create_single_work_requests(project_id,work_requests)
        #     i = i + 1
        #     #break

    def create_work_requests_by_project(self,project_id):
        for work_requests in self.get_trp_work_requests():
            self.create_single_work_requests(project_id,work_requests)
            #break

    def create_single_work_requests(self, project_id, work_requests):
        print(f"create_single_work_requests: project_id={project_id}")
        driver = self.automation.get_driver()
        url = self.automation.baseurl.rstrip('/') + '/' + f"translational-research/project/{project_id}/work-request/new/".lstrip('/')
        driver.get(url)
        time.sleep(3)

        #$client->executeScript("$('#oleg_translationalresearchbundle_request_products_".$productId."_requested').val('".$trpRequestArr['quantity'].
        #"')");
        #s2id_oleg_translationalresearchbundle_request_products_0_category
        #.select2-search .select2-input
        self.automation.select_option("s2id_oleg_translationalresearchbundle_request_products_0_category",
                                      "CSS_SELECTOR",
                                      "#select2-drop .select2-input",
                                      #"TRP-1002",
                                      work_requests['serviceName']
                                      )

        #$client->executeScript("$('#oleg_translationalresearchbundle_request_products_".$productId."_comment').val('".$trpRequestArr['comment']."')");
        time.sleep(3)
        quantity = driver.find_element(By.ID, "oleg_translationalresearchbundle_request_products_0_requested")
        quantity.send_keys(work_requests['quantity'])

        time.sleep(3)
        comment = driver.find_element(By.ID, "oleg_translationalresearchbundle_request_products_0_comment")
        comment.send_keys(work_requests['comment'])

        #$client->executeScript("$('#s2id_oleg_translationalresearchbundle_request_businessPurposes').select2('val','1')");
            #$client->executeScript('$("#s2id_oleg_translationalresearchbundle_request_businessPurposes")[0].scrollIntoView(false);');
        time.sleep(5)
        self.automation.select_option("s2id_oleg_translationalresearchbundle_request_businessPurposes",
                                      "CSS_SELECTOR",
                                      "#s2id_oleg_translationalresearchbundle_request_businessPurposes .select2-choices .select2-input",
                                      "Deliverable for the main project"
                                      )

        #Check #confirmationSubmit
        time.sleep(5)
        #$client->executeScript("$('#confirmationSubmit').prop('checked', true)");
        #$client->executeScript('$("#confirmationSubmit")[0].scrollIntoView(false);');
        # checkbox = driver.find_element(By.ID, 'confirmationSubmit')
        # if not checkbox.is_selected():
        #     checkbox.click()  # Check the checkbox
        # assert checkbox.is_selected(), "Checkbox is not selected!"

        try:
            blocking_element = driver.find_element(By.ID, 'select2-drop-mask')
            driver.execute_script("arguments[0].style.display = 'none';", blocking_element)  # Hide the element
        except Exception as e:
            print("Blocking element could not be removed:", e)
        # Now click the checkbox
        checkbox = driver.find_element(By.ID, 'confirmationSubmit')
        checkbox.click()
        time.sleep(3)

        #submit form #oleg_translationalresearchbundle_request_saveAsComplete
        self.automation.click_button_by_id("oleg_translationalresearchbundle_request_saveAsComplete")
        #print("New work request submitted")

        #get work request id
        time.sleep(3)
        #"https://view.online/c/demo-institution/demo-department/translational-research/work-request/show/20"
        current_url = driver.current_url
        #print(f"Current URL: {current_url}")
        work_request_id = current_url.split('/')[-1] #Assume work request id is the last element
        #print(f"Extracted Work Request ID: {work_request_id}")
        # driver.get('https://view.online/c/demo-institution/demo-department/translational-research/projects/')
        time.sleep(3)
        if work_request_id == None or work_request_id == '':
            print("Warning: work_request_id is empty. Invoice will not be generated.")
            return None

        time.sleep(3)
        self.create_invoice(work_request_id)


    def create_invoice(self, work_request_id):
        if work_request_id == None or work_request_id == '':
            print("New invoice not created, work_request_id is not provided")
            return None

        driver = self.automation.get_driver()
        url = self.automation.baseurl.rstrip('/') + '/' + f"translational-research/invoice/new/{work_request_id}".lstrip('/')
        driver.get(url)
        time.sleep(3)

        #click oleg_translationalresearchbundle_invoice_saveAndGeneratePdf
        self.automation.click_button_by_id("oleg_translationalresearchbundle_invoice_saveAndGeneratePdf")
        #print("New invoice submitted")
        time.sleep(3)

### End of class ###


def main():
    #url = "https://view.online/c/demo-institution/demo-department/directory/login"
    #username_text = "administrator"
    #password_text = "1234567890"
    #automation = WebAutomation()
    #automation.login_to_site(url, username_text, password_text)

    #trp = Trp(automation)
    #trp.create_projects()
    #trp.create_work_requests()
    #automation.quit_driver()

    run_by_symfony_command = True
    run_by_symfony_command = False

    print("Create projects")
    baseurl = "https://view.online/c/demo-institution/demo-department"
    automation = WebAutomation(baseurl, run_by_symfony_command)
    automation.login_to_site()
    trp = Trp(automation)
    trp.create_projects()
    time.sleep(3)
    automation.quit_driver()
    del automation

    print("Create work requests")
    automation = WebAutomation(baseurl, run_by_symfony_command)
    automation.login_to_site()
    trp.set_automation(automation)
    trp.create_work_requests()
    automation.quit_driver()
    del automation

    del trp

    print("TRP done!")

if __name__ == "__main__":
    main()


