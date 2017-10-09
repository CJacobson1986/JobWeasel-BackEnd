from unittest import TestSuite, TextTestRunner, TestLoader
import test_users
import test_skills
import test_jobs
import test_applications
import test_links


def make_suite():
    loader = TestLoader()
    suite = TestSuite()

    suite.addTests(loader.loadTestsFromModule(test_users))
    suite.addTests(loader.loadTestsFromModule(test_jobs))
    suite.addTests(loader.loadTestsFromModule(test_skills))
    suite.addTests(loader.loadTestsFromModule(test_applications))
    suite.addTests(loader.loadTestsFromModule(test_links))

    return suite

if __name__ == "__main__":
    runner = TextTestRunner()
    runner.run(make_suite())
