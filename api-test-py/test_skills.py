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



