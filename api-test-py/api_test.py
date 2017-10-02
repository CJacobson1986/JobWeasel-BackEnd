import random
import requests
import string
from unittest import TestCase


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


class ApiTestCase(TestCase):
    @classmethod
    def setUpClass(cls):
        cls.USED_ARGS = []
        cls.api_root = "http://localhost:8000/api/"

    @staticmethod
    def get_auth_header(token):
        return {
            "Authorization": "Bearer {}".format(token)
        }

    def rand_arg(self):
        arg = ''.join(random.choice(string.ascii_uppercase) for _ in range(10))
        if arg not in self.USED_ARGS:
            self.USED_ARGS.append(arg)
            return arg
        else:
            return self.rand_arg()

    def post(self, route, data, token=None):
        if not token:
            headers = {}
        else:
            headers = self.get_auth_header(token)
        response = requests.post(self.api_root + route, data=data, headers=headers)

        return response

    def get(self, route, params=None, token=None):
        if not token:
            headers = {}
        else:
            headers = self.get_auth_header(token)

        response = requests.get(self.api_root + route, params=params, headers=headers)

        return response

    def getUser(self, token):
        user = self.get("getUser", token=token).json()['user']

        return user

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

    def make_new_user(self, data):
        return self.post("signUp", data).json()["user"]

    def sign_in_new_user(self, data):
        self.post("signUp", data)
        response = self.post("signIn", data).json()

        return response["token"]
