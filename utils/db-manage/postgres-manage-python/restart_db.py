#!/usr/bin/python3
import argparse
import datetime
import logging
import subprocess
from subprocess import PIPE


def restart_postgres_databases(command):
    try:
        command = command.strip()
        output = subprocess.run([command], stdout=PIPE, stderr=PIPE, shell=True)
        return output
    except Exception as e:
        print(e)
        exit(1)

def main():
    logger = logging.getLogger(__name__)
    logger.setLevel(logging.INFO)
    handler = logging.StreamHandler()
    formatter = logging.Formatter('%(asctime)s - %(name)s - %(levelname)s - %(message)s')
    handler.setFormatter(formatter)
    logger.addHandler(handler)

    args_parser = argparse.ArgumentParser(description='Postgres database management')
    args_parser.add_argument("--command",
                             required=True)

    args = args_parser.parse_args()

    if args.command:
        command = args.command

    print("command=",command)
    res = "Unknown result: restart db. command {}".format(command)
    if command:
        res = restart_postgres_databases(command)

    return res;


if __name__ == '__main__':
    main()