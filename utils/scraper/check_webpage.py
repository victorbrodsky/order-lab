from selenium import webdriver
from selenium.webdriver.common.by import By


class Checker:
    def __init__(self, url):
        self.url = url

    # check if expected element exists on the web page
    # like: input type="hidden" id="heartbeatInput" name="status" value="alive"
    def check_element_on_webpage(self):
        # pass
        print("check_element_on_webpage")

        driver = webdriver.Chrome()

        # Navigate to the webpage
        driver.get(self.url)

        # Check if the element exists
        try:
            element = driver.find_element(By.ID, "heartbeatInput")
            print("Element exists.")
        except NoSuchElementException:
            print("Element does not exist.")

        # Close the browser
        driver.quit()









