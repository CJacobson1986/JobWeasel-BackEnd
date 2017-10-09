from unittest import TestSuite, TextTestRunner, TestLoader
from admin import ADMIN_DATA
from api_test import ApiTestCase
import test_users
import test_skills
import test_jobs
import test_applications
import test_links
import test_admin


def make_suite():
    loader = TestLoader()
    suite = TestSuite()

    suite.addTests(loader.loadTestsFromModule(test_admin))
    suite.addTests(loader.loadTestsFromModule(test_users))
    suite.addTests(loader.loadTestsFromModule(test_jobs))
    suite.addTests(loader.loadTestsFromModule(test_skills))
    suite.addTests(loader.loadTestsFromModule(test_applications))
    suite.addTests(loader.loadTestsFromModule(test_links))

    return suite

if __name__ == "__main__":
    try:
        ApiTestCase().make_new_user(ADMIN_DATA)
    except KeyError:
        pass
    runner = TextTestRunner(verbosity=3)
    runner.run(make_suite())
