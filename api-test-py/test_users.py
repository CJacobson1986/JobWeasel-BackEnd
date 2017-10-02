from unittest import TestCase
import requests
import random
import string


class User:
    USER = 'user'
    EMAIL = 'email'
    NAME = 'name'
    PASSWORD = 'password'
    BIO = 'bio'
    LOCATION = 'location'
    PHONE = 'phone'
    ID = 'id'
    ROLE_ID = 'role_id'
    SUCCESS = 'success'
    ERROR = 'error'


class TestUsers(TestCase):
    @classmethod
    def setUpClass(cls):
        cls.USED_ARGS = []
        cls.api_root = "http://localhost:8000/api/"

    def rand_arg(self):
        arg = ''.join(random.choice(string.ascii_uppercase) for _ in range(10))
        if arg not in self.USED_ARGS:
            self.USED_ARGS.append(arg)
            return arg
        else:
            return self.rand_arg()

    def get_sign_up_data(self, role_id):
        data = {
            User.NAME: self.rand_arg(),
            User.EMAIL: self.rand_arg(),
            User.PASSWORD: self.rand_arg(),
            User.ROLE_ID: role_id
        }

        return data

    def get_bio_data(self):
        data = {
            User.BIO: self.rand_arg(),
            User.LOCATION: self.rand_arg(),
            User.PHONE: random.randint(0, 9)
        }

        return data

    def post(self, route, data, headers=None):
        if not headers:
            headers = {}
        response = requests.post(self.api_root + route, data=data, headers=headers)

        return response

    def get(self, route, params=None, headers=None):
        if not params:
            params = {}
        if not headers:
            headers = {}

        response = requests.get(self.api_root + route, params=params, headers=headers)

        return response

    def getUser(self, token):
        user = self.get("getUser", headers={
            "Authorization": "Bearer " + token
        }).json()["user"]

        return user

    def editUser(self, data, token):
        user = self.post("editUser", data, headers={
            "Authorization": "Bearer " + token
        }).json()["user"]

        return user

    def test_sign_up(self):
        used_email = ""
        route = "signUp"

        for data in [self.get_sign_up_data(n) for n in range(1, 3)]:
            used_email = data[User.EMAIL]
            response = self.post(route, data).json()

            self.assertTrue(
                User.SUCCESS in response
            )
            test_user = response[User.USER]
            self.assertEqual(
                test_user[User.NAME], data[User.NAME]
            )
            self.assertEqual(
                test_user[User.EMAIL], data[User.EMAIL]
            )
            self.assertEqual(
                int(test_user[User.ROLE_ID]), data[User.ROLE_ID]
            )

        bad_data = self.get_sign_up_data(1)
        bad_data.pop(User.EMAIL)
        bad_response = self.post(route, bad_data).json()
        self.assertTrue(
            User.ERROR in bad_response
        )

        bad_data[User.EMAIL] = used_email
        response = self.post(route, bad_data).json()
        self.assertTrue(
            User.ERROR in response
        )

    def test_sign_in(self):
        data = self.get_sign_up_data(1)
        self.post('signUp', data).json()
        response = self.post("signIn", data).json()

        self.assertTrue(
            "success" in response
        )
        self.assertTrue(
            "token" in response
        )

        user = self.getUser(response["token"])
        self.assertEqual(
            data["name"], user["name"]
        )

        data["password"] = "wrong"
        bad_response = self.post("signIn", data).json()

        self.assertTrue(
            "error" in bad_response
        )

    def test_show(self):
        data = self.get_sign_up_data(1)
        user = self.post('signUp', data).json()[User.USER]
        response = self.get("showUser/" + str(user[User.ID])).json()
        test_user = response["user"]

        self.assertEqual(
            data["name"], test_user["name"]
        )

    def test_index(self):
        response = self.get('getUsers').json()
        self.assertTrue(
            "users" in response
        )
        self.assertTrue(
            "data" in response["users"]
        )

    def test_update(self):
        data = self.get_sign_up_data(1)
        self.post('signUp', data).json()
        response = self.post("signIn", data).json()

        token = response["token"]
        update_data = self.get_bio_data()
        self.editUser(update_data, token)

        user_id = response['user']['id']
        user = self.get("showUser/" + str(user_id)).json()['user']

        self.assertEqual(
            user[User.BIO], update_data[User.BIO]
        )
        self.assertEqual(
            user[User.LOCATION], update_data[User.LOCATION]
        )
        self.assertEqual(
            user[User.PHONE], update_data[User.PHONE]
        )
