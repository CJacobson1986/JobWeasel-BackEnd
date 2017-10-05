from api_test import ApiTestCase, User


class TestUsers(ApiTestCase):
    def test_sign_up(self):
        used_email = ""
        route = "signUp"

        # job seeker and job poster sign up
        for data in [self.get_sign_up_data(n) for n in range(1, 3)]:
            used_email = data[User.EMAIL]
            response = self.post(route, data).json()

            # user data matches submission data
            self.assertTrue(
                User.SUCCESS in response
            )
            test_user = response[User.USER]
            data.pop(User.PASSWORD)
            self.assertTrue(
                all(self.compare_data_to_response(data, test_user))
            )

        # ERROR: not all fields provided
        bad_data = self.get_sign_up_data(1)
        bad_data.pop(User.EMAIL)
        bad_response = self.post(route, bad_data).json()
        self.assertTrue(
            User.ERROR in bad_response
        )

        # ERROR: user signs up with duplicate email
        bad_data[User.EMAIL] = used_email
        response = self.post(route, bad_data).json()
        self.assertTrue(
            User.ERROR in response
        )

    def test_sign_in(self):
        # new user signs up
        data = self.get_sign_up_data(1)

        # new user signs in
        self.post('signUp', data).json()
        response = self.post("signIn", data).json()

        self.assertTrue(
            "success" in response
        )
        self.assertTrue(
            "token" in response
        )

        # user data matches sign in data
        user = self.getUser(response["token"])
        self.assertEqual(
            data["name"], user["name"]
        )

        # ERROR: wrong password used for sign in
        data["password"] = "wrong"
        bad_response = self.post("signIn", data).json()

        self.assertTrue(
            "error" in bad_response
        )

    def test_show_user(self):
        # new user signs up
        data = self.get_sign_up_data(1)
        new_user = self.make_new_user(data)

        # user views user by id
        user_id = new_user["id"]
        response = self.get("showUser/{}".format(user_id)).json()
        user = response["user"]

        # response data matches user data
        self.assertEqual(
            data["name"], user["name"]
        )

    def test_get_users(self):
        # new user signs up
        data = self.get_sign_up_data(1)
        self.make_new_user(data)

        response = self.get('getUsers').json()
        self.assertTrue(
            "users" in response
        )
        self.assertTrue(
            "data" in response["users"]
        )

    def test_edit_user(self):
        # new user signs up
        token = self.sign_in_new_user(
            self.get_sign_up_data(1)
        )

        # user updates bio
        update_data = self.get_bio_data()
        user = self.post(
            "editUser", update_data, token=token
        ).json()[User.USER]

        # user data matches update data
        self.assertTrue(
            all(self.compare_data_to_response(update_data, user))
        )
