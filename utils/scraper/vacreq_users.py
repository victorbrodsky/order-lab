#NOT USED
class UserManager:
    """A class to manage user-related data."""

    @staticmethod
    def get_vacreqs():
        """
        Retrieves vacation requests.
        
        Returns:
            list: A list of user dictionaries.
        """
        users = [
            {'groupId': 29, 'userId': 15, 'cwid': 'aeinstein'},
            {'groupId': 29, 'userId': 16, 'cwid': 'rrutherford'},
            {'groupId': 29, 'userId': 12, 'cwid': 'johndoe'},
            {'groupId': 29, 'userId': 2, 'cwid': 'administrator'}
        ]
        return users

    @staticmethod
    def get_users():
        """
        Retrieves users with their details.
        
        Returns:
            list: A list of user dictionaries containing user details.
        """
        users = [
            {
                'userid': 'johndoe',
                'firstName': 'John',
                'lastName': 'Doe',
                'displayName': 'John Doe',
                'email': 'cinava@yahoo.com',
                'password': 'pass',
                'roles': ['ROLE_USERDIRECTORY_OBSERVER'],
                'userId': 12
            },
            {
                'userid': 'aeinstein',
                'firstName': 'Albert',
                'lastName': 'Einstein',
                'displayName': 'Albert Einstein',
                'email': 'cinava@yahoo.com',
                'password': 'pass',
                'roles': ['ROLE_USERDIRECTORY_OBSERVER'],
                'userId': 15
            },
            {
                'userid': 'rrutherford',
                'firstName': 'Ernest',
                'lastName': 'Rutherford',
                'displayName': 'Ernest Rutherford',
                'email': 'cinava@yahoo.com',
                'password': 'pass',
                'roles': ['ROLE_USERDIRECTORY_OBSERVER'],
                'userId': 16
            }
        ]
        return users

# Example usage:
user_manager = UserManager()
users = user_manager.get_users()
print(users)

