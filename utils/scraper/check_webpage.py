from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.common.exceptions import NoSuchElementException

class Checker:
    #def __init__(self, url):
    #    self.url = url

    # check if expected element exists on the web page
    # like: input type="hidden" id="heartbeatInput" name="status" value="alive"
    def check_element_on_webpage(self,url):
        # pass
        print("###check_element_on_webpage###")
        status = False

        #driver = webdriver.Chrome()
        options = webdriver.ChromeOptions()
        options.add_argument("--headless")
        options.add_argument("--no-sandbox")  # working in command. Disable the Chrome sandbox, which is a security feature that isolates browser processes
        options.add_argument("--disable-dev-shm-usage")  # working in command. Prevent Chrome from using shared memory

        #options.add_experimental_option("detach", True)
        driver = webdriver.Chrome(options=options)

        print("###check_element_on_webpage: before driver.get(url) ###")

        # Navigate to the webpage
        driver.get(url)

        print("###check_element_on_webpage: after driver.get(url) ###")

        # Check if the element exists
        try:
            element = driver.find_element(By.ID, "heartbeatInput")
            print("###Element heartbeatInput exists.###")
            status = True
        except NoSuchElementException:
            print("###Element heartbeatInput does not exist.###")

        # Close the browser
        driver.quit()

        print(f"###check_element_on_webpage: return status={status} ###")

        return status

if __name__ == "__main__":
    checker = Checker()
    #url = "https://view.online/c/wcm/pathology"
    url = "https://view-test.med.cornell.edu"
    print("url=",url)
    checker.check_element_on_webpage(url)







