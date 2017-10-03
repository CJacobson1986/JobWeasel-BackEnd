from api_test import ApiTestCase


class TestApplications(ApiTestCase):
    def test_get_applications(self):
        # jobs poster signs up
        token = self.sign_in_new_user(
            self.get_sign_up_data(1)
        )

        # job poster submits job
        data = self.get_job_data()
        response = self.post("postJob", data, token=token).json()
        new_job = response["job"]
        job_id = new_job["id"]

        # job seeker signs up
        token = self.sign_in_new_user(
            self.get_sign_up_data(2)
        )
        applicant = self.getUser(token)
        user_id = applicant["id"]

        # job seeker applies for job
        self.post(
            "submitApplication", {
                "job_id": job_id
            }, token=token
        ).json()

        # job poster views applications for their job
        response = self.get(
            "getApplications/{}".format(job_id)
        ).json()
        apps = response["applications"]
        newest = apps[-1]

        # job seeker's application should be newest entry
        self.assertEqual(
            int(newest["user_id"]), user_id
        )

    def test_submit_application(self):
        # job poster signs up
        token = self.sign_in_new_user(
            self.get_sign_up_data(1)
        )

        # job poster posts job
        data = self.get_job_data()
        response = self.post("postJob", data, token=token).json()
        new_job = response["job"]
        job_id = new_job["id"]

        # job seeker signs up
        token = self.sign_in_new_user(
            self.get_sign_up_data(2)
        )

        # job seeker applies for job
        response = self.post(
            "submitApplication", {
                "job_id": job_id
            }, token=token
        ).json()
        app = response["application"]

        # application matches submission data
        self.assertEqual(
            int(app["job_id"]), job_id
        )
        self.assertEqual(
            int(app["employer_approves"]), 0
        )
        self.assertEqual(
            int(app["applicant_reviewed"]), 0
        )
        self.assertEqual(
            int(app["employee_accepts"]), 0
        )

    def test_review_application(self):
        # job poster signs up
        poster_token = self.sign_in_new_user(
            self.get_sign_up_data(1)
        )

        # job poster submits job
        data = self.get_job_data()
        response = self.post("postJob", data, token=poster_token).json()
        new_job = response["job"]
        job_id = new_job["id"]

        # job seeker signs up
        seeker_token = self.sign_in_new_user(
            self.get_sign_up_data(2)
        )

        # job seeker applies for job
        app = self.post(
            "submitApplication", {
                "job_id": job_id
            }, token=seeker_token
        ).json()["application"]
        app_id = app["id"]

        # job poster reviews applications
        review_data = {
            "employer_approves": 1,
            "application_id": app_id
        }
        response = self.post(
            "reviewApplication", review_data,
            token=poster_token
        ).json()
        app = response["application"]

        # job application reviewed successfully
        self.assertEqual(
            int(app["employer_approves"]), 1
        )

        # ERROR: job seeker tries to approve their own application
        self.post(
            "submitApplication", {
                "job_id": job_id
            }, token=seeker_token
        ).json()

        review_data = {
            "employer_approves": 1,
            "application_id": app_id
        }
        response = self.post(
            "reviewApplication", review_data,
            token=seeker_token
        ).json()
        self.assertTrue(
            "error" in response
        )

    def test_accept_offer(self):
        # poster signs up
        poster_token = self.sign_in_new_user(
            self.get_sign_up_data(1)
        )

        # poster posts job
        data = self.get_job_data()
        response = self.post("postJob", data, token=poster_token).json()
        new_job = response["job"]
        job_id = new_job["id"]

        # seeker signs up
        seeker_token = self.sign_in_new_user(
            self.get_sign_up_data(2)
        )

        # seeker applies for job
        app = self.post(
            "submitApplication", {
                "job_id": job_id
            }, token=seeker_token
        ).json()["application"]
        app_id = app["id"]

        # job poster approves application
        review_data = {
            "employer_approves": 1,
            "application_id": app_id
        }
        self.post(
            "reviewApplication", review_data,
            token=poster_token
        )

        # job seeker accepts job offer
        accept_data = {
            "application_id": app_id,
            "employee_accepts": 1
        }
        response = self.post(
            "acceptOffer", accept_data,
            token=seeker_token
        ).json()

        self.assertTrue(
            "success" in response
        )

        # ERROR: You are not the submitter of this application
        accept_data = {
            "application_id": app_id,
            "employee_accepts": 1
        }
        response = self.post(
            "acceptOffer", accept_data,
            token=poster_token
        ).json()

        self.assertTrue(
            "error" in response
        )

        # ERROR: This application has not been reviewed/approved
        # poster posts job
        data = self.get_job_data()
        response = self.post("postJob", data, token=poster_token).json()
        new_job = response["job"]
        job_id = new_job["id"]

        # seeker submits application
        app = self.post(
            "submitApplication", {
                "job_id": job_id
            }, token=seeker_token
        ).json()["application"]
        app_id = app["id"]

        # seeker tries to accept application that isn't reviewed
        accept_data = {
            "application_id": app_id,
            "employee_accepts": 1
        }
        response = self.post(
            "acceptOffer", accept_data,
            token=seeker_token
        ).json()

        self.assertTrue(
            "error" in response
        )

        # ERROR: This application has been reviewed but was rejected
        # poster posts job
        data = self.get_job_data()
        response = self.post("postJob", data, token=poster_token).json()
        new_job = response["job"]
        job_id = new_job["id"]

        # seeker submits application
        app = self.post(
            "submitApplication", {
                "job_id": job_id
            }, token=seeker_token
        ).json()["application"]
        app_id = app["id"]

        # job poster rejects application
        review_data = {
            "employer_approves": 0,
            "application_id": app_id
        }
        self.post(
            "reviewApplication", review_data,
            token=poster_token
        )

        # seeker tries to accept application that was rejected
        accept_data = {
            "application_id": app_id,
            "employee_accepts": 1
        }
        response = self.post(
            "acceptOffer", accept_data,
            token=seeker_token
        ).json()

        self.assertTrue(
            "error" in response
        )
