from api_test import ApiTestCase


class Skills:
    NAME = "name"


class TestSkills(ApiTestCase):
    def get_skill_data(self):
        data = {
            Skills.NAME: self.rand_arg()
        }

        return data

    def test_add_skill(self):
        token = self.sign_in_new_user(
            self.get_sign_up_data(1)
        )

        skill_data = self.get_skill_data()
        response = self.post("addSkill", skill_data, token).json()

        skill = response["skill"]
        self.assertEqual(
            skill[Skills.NAME], skill_data[Skills.NAME]
        )

    def test_get_skills(self):
        token = self.sign_in_new_user(
            self.get_sign_up_data(1)
        )

        skill_data = self.get_skill_data()
        self.post("addSkill", skill_data, token)

        response = self.get("getSkills").json()
        newest = response["skills"][-1]

        self.assertTrue(
            "skills" in response
        )
        self.assertEqual(
            newest[Skills.NAME], skill_data[Skills.NAME]
        )

    def test_add_user_skill(self):
        token = self.sign_in_new_user(
            self.get_sign_up_data(1)
        )

        data = {"skill_id": 1}
        response = self.post("addUserSkill", data, token=token).json()
        user_skill = response["user_skill"]
        user_id = self.getUser(token)["id"]

        self.assertEqual(
            int(user_skill["skill_id"]), data["skill_id"]
        )
        self.assertEqual(
            int(user_skill["user_id"]), user_id
        )

    def test_get_user_skills(self):
        token = self.sign_in_new_user(
            self.get_sign_up_data(1)
        )

        skill_data = self.get_skill_data()
        response = self.post("addSkill", skill_data, token).json()
        skill = response["skill"]
        skill_id = skill["id"]
        data = {"skill_id": skill_id}
        self.post("addUserSkill", data, token=token).json()

        user_id = self.getUser(token)["id"]
        skills = self.get(
            "getUserSkills/{}".format(user_id)
        ).json()["skills"]

        newest = skills[-1]
        self.assertEqual(
            newest["id"], skill_id
        )
        self.assertEqual(
            newest["name"], skill_data["name"]
        )

    def test_index_user_skills(self):
        token = self.sign_in_new_user(
            self.get_sign_up_data(1)
        )

        skill_data = self.get_skill_data()
        self.post("addSkill", skill_data, token)
        user_id = self.getUser(token)["id"]
        skill_id = self.get("getSkills").json()["skills"][-1]["id"]
        user_skill_data = {
            "skill_id": skill_id,
            "user_id": user_id
        }
        self.post("addUserSkill", user_skill_data, token)

        response = self.get("getUserSkills").json()
        user_skills = response["user_skills"]

        self.assertTrue(
            "user_skills" in response
        )

        name = skill_data["name"]

        def get_skill_name(row):
            for skill in self.get("getSkills").json()["skills"]:
                if row["skill_id"] == skill["id"]:
                    return skill["name"]

        skill_names = [get_skill_name(row) for row in user_skills]

        self.assertTrue(
            name in skill_names
        )
