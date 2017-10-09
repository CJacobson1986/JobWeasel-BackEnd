from api_test import ApiTestCase


class TestLinks(ApiTestCase):
    def get_link_data(self, job=None):
        data = {
            "text": self.rand_arg(),
            "url": self.rand_arg(),
        }
        if job:
            data["job_id"] = job

        return data

    def test_add_link_to_job(self):
        # job poster signs up
        token = self.sign_in_new_user(
            self.get_sign_up_data(1)
        )

        # job poster submits job
        data = self.get_job_data()
        response = self.post("postJob", data, token=token).json()
        new_job = response["job"]

        # job poster adds link to job
        link_data = self.get_link_data(job=new_job["id"])
        response = self.post("addLinkToJob", link_data, token).json()
        new_job_link = response["link"]

        # link matches submission data
        self.assertTrue(
            all(self.compare_data_to_response(link_data, new_job_link))
        )

        # other job poster signs up
        other_token = self.sign_in_new_user(
            self.get_sign_up_data(1)
        )

        # other job poster submits job
        data = self.get_job_data()
        response = self.post("postJob", data, token=other_token).json()
        other_id = response["job"]["id"]

        # ERROR: job poster adds link to other job
        link_data = self.get_link_data(job=other_id)
        response = self.post("addLinkToJob", link_data, token).json()

        self.assertTrue(
            "error" in response
        )

    def test_add_link_to_user(self):
        # job seeker signs up
        token = self.sign_in_new_user(
            self.get_sign_up_data(2)
        )

        # user adds link to profile
        link_data = self.get_link_data()
        response = self.post("addLinkToUser", link_data, token).json()
        new_user_link = response["link"]

        self.assertTrue(
            all(self.compare_data_to_response(link_data, new_user_link))
        )

    def test_get_user_links(self):
        # job seeker signs up
        token = self.sign_in_new_user(
            self.get_sign_up_data(2)
        )

        # user adds link to profile
        link_data = self.get_link_data()
        link_id = self.post("addLinkToUser", link_data, token).json()["link"]["id"]

        # link is in list of user links
        response = self.get("getUserLinks/{}".format(
            self.getUser(token)["id"]
        )).json()

        self.assertTrue(
            link_id in [int(l["id"]) for l in response["links"]]
        )

    def test_get_job_links(self):
        # job poster signs up
        token = self.sign_in_new_user(
            self.get_sign_up_data(1)
        )

        # job poster submits job
        data = self.get_job_data()
        response = self.post("postJob", data, token=token).json()
        new_job = response["job"]

        # job poster adds link to job
        link_data = self.get_link_data(job=new_job["id"])
        link_id = self.post("addLinkToJob", link_data, token).json()["link"]["id"]

        # link is in list of job links
        response = self.get("getJobLinks/{}".format(
            new_job["id"]
        )).json()

        self.assertTrue(
            link_id in [int(l["id"]) for l in response["links"]]
        )

    def test_edit_link(self):
        # job seeker signs up
        token = self.sign_in_new_user(
            self.get_sign_up_data(2)
        )

        # user adds link to profile
        link_data = self.get_link_data()
        response = self.post("addLinkToUser", link_data, token).json()
        link = response["link"]

        # user edits link
        link_data["text"] = "new text"
        link_data["url"] = "new url"
        link_data["link_id"] = link["id"]
        response = self.post("editLink", link_data, token).json()
        link_data.pop("link_id")

        # link matches new updated data
        self.assertTrue(
            all(self.compare_data_to_response(link_data, response["link"]))
        )
