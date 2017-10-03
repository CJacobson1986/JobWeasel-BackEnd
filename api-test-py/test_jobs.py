from api_test import ApiTestCase, Jobs


class TestJobs(ApiTestCase):
    def test_post_job(self):
        # job poster signs u
        token = self.sign_in_new_user(
            self.get_sign_up_data(1)
        )

        # job poster submits job
        data = self.get_job_data()
        response = self.post("postJob", data, token=token).json()
        new_job = response["job"]

        # job data matches submission data
        self.assertEqual(
            new_job[Jobs.NAME], data[Jobs.NAME]
        )
        self.assertEqual(
            new_job[Jobs.DESCRIPTION], data[Jobs.DESCRIPTION]
        )
        self.assertEqual(
            int(new_job[Jobs.WORKERS_NEEDED]), data[Jobs.WORKERS_NEEDED]
        )
        self.assertEqual(
            new_job[Jobs.START_DATE], data[Jobs.START_DATE]
        )
        self.assertEqual(
            int(new_job[Jobs.TIME_FRAME]), data[Jobs.TIME_FRAME]
        )
        self.assertEqual(
            int(new_job[Jobs.BUDGET]), data[Jobs.BUDGET]
        )

        # job seeker signs up
        token = self.sign_in_new_user(
            self.get_sign_up_data(2)
        )

        # ERROR: job seeker tries to post job listing
        data = self.get_job_data()
        response = self.post("postJob", data, token=token).json()

        self.assertTrue(
            "error" in response
        )

    def test_get_jobs(self):
        # job poster signs up
        token = self.sign_in_new_user(
            self.get_sign_up_data(1)
        )

        # job poster submits job
        data = self.get_job_data()
        self.post(
            "postJob", data, token=token
        )

        # user views jobs that have been posted
        response = self.get("getJobs").json()
        self.assertTrue(
            "data" in response["jobs"]
        )
        newest = response["jobs"]["data"][0]

        # newest job posted matches submission data
        self.assertEqual(
            newest[Jobs.NAME], data[Jobs.NAME]
        )
        self.assertEqual(
            newest[Jobs.DESCRIPTION], data[Jobs.DESCRIPTION]
        )
        self.assertEqual(
            int(newest[Jobs.WORKERS_NEEDED]), data[Jobs.WORKERS_NEEDED]
        )
        self.assertEqual(
            newest[Jobs.START_DATE], data[Jobs.START_DATE]
        )
        self.assertEqual(
            int(newest[Jobs.TIME_FRAME]), data[Jobs.TIME_FRAME]
        )
        self.assertEqual(
            int(newest[Jobs.BUDGET]), data[Jobs.BUDGET]
        )

    def test_show_job(self):
        # job poster signs up
        token = self.sign_in_new_user(
            self.get_sign_up_data(1)
        )

        # job poster submits job
        data = self.get_job_data()
        job = self.post(
            "postJob", data, token=token
        ).json()["job"]
        job_id = job["id"]

        # user views that job
        response = self.get("showJob/{}".format(job_id)).json()
        test_job = response["job"]

        # job data matches submission data
        self.assertEqual(
            test_job[Jobs.NAME], data[Jobs.NAME]
        )
        self.assertEqual(
            test_job[Jobs.DESCRIPTION], data[Jobs.DESCRIPTION]
        )
        self.assertEqual(
            int(test_job[Jobs.WORKERS_NEEDED]), data[Jobs.WORKERS_NEEDED]
        )
        self.assertEqual(
            test_job[Jobs.START_DATE], data[Jobs.START_DATE]
        )
        self.assertEqual(
            int(test_job[Jobs.TIME_FRAME]), data[Jobs.TIME_FRAME]
        )
        self.assertEqual(
            int(test_job[Jobs.BUDGET]), data[Jobs.BUDGET]
        )

    def test_edit_job(self):
        # job poster signs up
        token = self.sign_in_new_user(
            self.get_sign_up_data(1)
        )

        # job poster submits job
        data = self.get_job_data()
        job = self.post(
            "postJob", data, token=token
        ).json()["job"]
        job_id = job["id"]

        # job poster edits job description
        new_job_data = self.get_job_data()
        new_job_data["job_id"] = job_id
        response = self.post(
            "editJob", new_job_data, token=token
        ).json()

        # edited job data matches submission data
        new_job = response["job"]
        self.assertEqual(
            new_job[Jobs.NAME], new_job_data[Jobs.NAME]
        )
        self.assertEqual(
            new_job[Jobs.DESCRIPTION], new_job_data[Jobs.DESCRIPTION]
        )
        self.assertEqual(
            int(new_job[Jobs.WORKERS_NEEDED]), new_job_data[Jobs.WORKERS_NEEDED]
        )
        self.assertEqual(
            new_job[Jobs.START_DATE], new_job_data[Jobs.START_DATE]
        )
        self.assertEqual(
            int(new_job[Jobs.TIME_FRAME]), new_job_data[Jobs.TIME_FRAME]
        )
        self.assertEqual(
            int(new_job[Jobs.BUDGET]), new_job_data[Jobs.BUDGET]
        )

        # job poster signs up
        token = self.sign_in_new_user(
            self.get_sign_up_data(1)
        )

        # ERROR: job poster tries to edit job submitted by another user
        response = self.post(
            "editJob", new_job_data, token=token
        ).json()
        self.assertTrue(
            "error" in response
        )
