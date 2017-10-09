from api_test import ApiTestCase
from admin import ADMIN_DATA


class TestAdmin(ApiTestCase):
    def get_super_admin_token(self):
        return self.post(
            "signIn", ADMIN_DATA
        ).json()["token"]

    def test_make_admin(self):
        # super admin logs in
        super_token = self.get_super_admin_token()

        # new user signs up
        token = self.sign_in_new_user(
            self.get_sign_up_data(1)
        )

        # new user assigned admin status
        data = {
            "user_id": self.getUser(token)["id"]
        }
        response = self.post("makeAdmin", data, token=super_token).json()

        self.assertEqual(
            response["user"]["id"], data["user_id"]
        )

        # ERROR: admin status assigned to user already in admins
        response = self.post("makeAdmin", data, token=super_token).json()

        self.assertTrue(
            "error" in response
        )

        # ERROR: non super admin tries to set admin status
        other_user = self.getUser(self.sign_in_new_user(
            self.get_sign_up_data(1)
        ))

        data = {
            "user_id": other_user["id"]
        }
        response = self.post("makeAdmin", data, token=token).json()

        self.assertTrue(
            "error" in response
        )

    def test_get_admins(self):
        # super admin logs in
        super_token = self.get_super_admin_token()

        # new user signs up
        token = self.sign_in_new_user(
            self.get_sign_up_data(1)
        )

        # new user assigned admin status
        data = {
            "user_id": self.getUser(token)["id"]
        }
        self.post("makeAdmin", data, token=super_token).json()

        admins = self.get("getAdmins").json()["admins"]

        self.assertTrue(
            data["user_id"] in [u["id"] for u in admins]
        )

    def test_delete_admin(self):
        # super admin logs in
        super_token = self.get_super_admin_token()

        # new user signs up
        token = self.sign_in_new_user(
            self.get_sign_up_data(1)
        )

        # new user assigned admin status
        user_data = {
            "user_id": self.getUser(token)["id"]
        }
        admin_id = self.post(
            "makeAdmin", user_data, token=super_token
        ).json()["admin"]["id"]

        # super admin removes admin status from user
        data = {
            "admin_id": admin_id
        }
        response = self.post("removeAdmin", data, token=super_token).json()

        self.assertEqual(
            response["user"]["id"], user_data["user_id"]
        )

        # ERROR: delete admin that doesn't exist
        response = self.post("removeAdmin", data, token=super_token).json()

        self.assertTrue(
            "error" in response
        )

        # ERROR: non super admin tries to remove admin status
        # new user assigned admin status
        user_data = {
            "user_id": self.getUser(token)["id"]
        }
        admin_id = self.post(
            "makeAdmin", user_data, token=super_token
        ).json()["admin"]["id"]
        data = {
            "admin_id": admin_id
        }
        response = self.post("removeAdmin", data, token=token).json()

        self.assertTrue(
            "error" in response
        )
