from os import system
from unittest import main

def reset_db():
    system("php artisan migrate:reset")
    system("php artisan migrate")
    system("php artisan serve")

if __name__ == "__main__":
    reset_db()