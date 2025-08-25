#!/usr/bin/python3
import argparse
import datetime
import logging
import subprocess
import os
import shutil
import tempfile
from tempfile import mkstemp

#import configparser
import gzip
import boto3
import psycopg2
from psycopg2.extensions import ISOLATION_LEVEL_AUTOCOMMIT

#use the PyYAML library to parse the YAML
import yaml

# Amazon S3 settings.
# AWS_ACCESS_KEY_ID  in ~/.aws/credentials
# AWS_SECRET_ACCESS_KEY in ~/.aws/credentials

#db.config:
#cp "$bashpath"/order-lab/utils/db-manage/postgres-manage-python/sample.config "$bashpath"/order-lab/utils/db-manage/postgres-manage-python/db.config
#Edit utils/db-manage/postgres-manage-python/db.config

def upload_to_s3(file_full_path, dest_file, manager_config):
    pass
    # """
    # Upload a file to an AWS S3 bucket.
    # """
    # s3_client = boto3.client('s3')
    # try:
    #     s3_client.upload_file(file_full_path,
    #                           manager_config.get('AWS_BUCKET_NAME'),
    #                           manager_config.get('AWS_BUCKET_PATH') + dest_file)
    #     os.remove(file_full_path)
    # except boto3.exceptions.S3UploadFailedError as exc:
    #     print(exc)
    #     exit(1)


def download_from_s3(backup_s3_key, dest_file, manager_config):
    """
    Upload a file to an AWS S3 bucket.
    """
    s3_client = boto3.resource('s3')
    try:
        s3_client.meta.client.download_file(manager_config.get('AWS_BUCKET_NAME'), backup_s3_key, dest_file)
    except Exception as e:
        print(e)
        exit(1)


def list_available_backups(storage_engine, manager_config):
    key_list = []
    #backup_list = []
    #print("storage_engine=",storage_engine)
    if storage_engine == 'LOCAL':
        #print("Local storage")
        try:
            #print("Local storage")
            backup_folder = manager_config.get('LOCAL_BACKUP_PATH')
            backup_list = os.listdir(backup_folder)
            #print("backup_list",backup_list)
        except FileNotFoundError:
            print(f'Could not found {backup_folder} when searching for backups.'
                  f'Check your .config file settings')
            exit(1)
    elif storage_engine == 'S3':
        pass
        #print("S3 storage")
        # logger.info('Listing S3 bucket s3://{}/{} content :'.format(aws_bucket_name, aws_bucket_path))
        #s3_client = boto3.client('s3')
        #s3_objects = s3_client.list_objects_v2(Bucket=manager_config.get('AWS_BUCKET_NAME'),
        #                                       Prefix=manager_config.get('AWS_BUCKET_PATH'))
        #backup_list = [s3_content['Key'] for s3_content in s3_objects['Contents']]
    else:
        print("Invalid storage_engine=", storage_engine)
        exit(1)

    for bckp in backup_list:
        key_list.append(bckp)
    #print("key_list=", key_list)
    return key_list


def list_postgres_databases(host, database_name, port, user, password):
    try:
        process = subprocess.Popen(
            ['psql',
             '--dbname=postgresql://{}:{}@{}:{}/{}'.format(user, password, host, port, database_name),
             '--list'],
            stdout=subprocess.PIPE
        )
        output = process.communicate()[0]
        if int(process.returncode) != 0:
            print('Command failed. Return code : {}'.format(process.returncode))
            exit(1)
        return output
    except Exception as e:
        print(e)
        exit(1)


def backup_postgres_db(host, database_name, port, user, password, dest_file, verbose):
    """
    Backup postgres db to a file.
    """
    print(f'backup_postgres_db: dest_file={dest_file}')
    os.makedirs(os.path.dirname(dest_file), exist_ok=True)

    # Check if the file exists, and create it if it doesn't
    if not os.path.exists(dest_file):
        with open(dest_file, 'w') as f:
            pass  # Creates an empty file
        print("File created:", dest_file)
    else:
        print("File already exists:", dest_file)

    if verbose:
        print(f"backup_postgres_db: verbose={verbose}")
        try:
            process = subprocess.Popen(
                ['pg_dump',
                 '--dbname=postgresql://{}:{}@{}:{}/{}'.format(user, password, host, port, database_name),
                 '-Fc',
                 '-f', dest_file,
                 '-v'],
                stdout=subprocess.PIPE
            )
            output = process.communicate()[0]
            print('backup_postgres_db: verbose Command. Return code : {}'.format(process.returncode))
            if int(process.returncode) != 0:
                print('Command failed. Return code : {}'.format(process.returncode))
                exit(1)
            return output
        except Exception as e:
            print(e)
            exit(1)
    else:
        print(f"backup_postgres_db: non verbose={verbose}")
        try:
            process = subprocess.Popen(
                ['pg_dump',
                 '--dbname=postgresql://{}:{}@{}:{}/{}'.format(user, password, host, port, database_name),
                 '-f', dest_file],
                stdout=subprocess.PIPE
            )
            output = process.communicate()[0]
            print('backup_postgres_db: Command. Return code : {}'.format(process.returncode))
            if process.returncode != 0:
                print('Command failed. Return code : {}'.format(process.returncode))
                exit(1)
            return output
        except Exception as e:
            print(e)
            exit(1)


def compress_file(src_file):
    compressed_file = "{}.gz".format(str(src_file))
    with open(src_file, 'rb') as f_in:
        with gzip.open(compressed_file, 'wb') as f_out:
            for line in f_in:
                f_out.write(line)
    return compressed_file


def extract_file(src_file):
    extracted_file, extension = os.path.splitext(src_file)

    with gzip.open(src_file, 'rb') as f_in:
        with open(extracted_file, 'wb') as f_out:
            for line in f_in:
                f_out.write(line)
    return extracted_file


def remove_faulty_statement_from_dump(src_file):
    temp_file, _ = tempfile.mkstemp()

    try:
        with open(temp_file, 'w+'):
            process = subprocess.Popen(
                ['pg_restore',
                 '-l'
                 '-v',
                 src_file],
                stdout=subprocess.PIPE
            )
            output = subprocess.check_output(('grep', '-v', '"EXTENSION - plpgsql"'), stdin=process.stdout)
            process.wait()
            if int(process.returncode) != 0:
                print('Command failed. Return code : {}'.format(process.returncode))
                exit(1)

            os.remove(src_file)
            with open(src_file, 'w+') as cleaned_dump:
                subprocess.call(
                    ['pg_restore',
                     '-L'],
                    stdin=output,
                    stdout=cleaned_dump
                )

    except Exception as e:
        print("Issue when modifying dump : {}".format(e))


def change_user_from_dump(source_dump_path, old_user, new_user):
    fh, abs_path = mkstemp()
    with os.fdopen(fh, 'w') as new_file:
        with open(source_dump_path) as old_file:
            for line in old_file:
                new_file.write(line.replace(old_user, new_user))
    # Remove original file
    os.remove(source_dump_path)
    # Move new file
    shutil.move(abs_path, source_dump_path)


def restore_postgres_db(db_host, db, port, user, password, backup_file, verbose):
    """Restore postgres db from a file."""
    try:
        subprocess_params = [
            'pg_restore',
            '--no-owner',
            '--dbname=postgresql://{}:{}@{}:{}/{}'.format(user,
                                                          password,
                                                          db_host,
                                                          port,
                                                          db)
        ]

        if verbose:
            subprocess_params.append('-v')

        subprocess_params.append(backup_file)
        process = subprocess.Popen(subprocess_params, stdout=subprocess.PIPE)
        output = process.communicate()[0]

        if int(process.returncode) != 0:
            print('Command failed. Return code : {}'.format(process.returncode))

        return output
    except Exception as e:
        print("Issue with the db restore : {}".format(e))


def create_db(db_host, database, db_port, user_name, user_password):
    try:
        con = psycopg2.connect(dbname='postgres', port=db_port,
                               user=user_name, host=db_host,
                               password=user_password)

    except Exception as e:
        print(e)
        exit(1)

    con.set_isolation_level(ISOLATION_LEVEL_AUTOCOMMIT)
    cur = con.cursor()
    try:
        cur.execute("SELECT pg_terminate_backend( pid ) "
                    "FROM pg_stat_activity "
                    "WHERE pid <> pg_backend_pid( ) "
                    "AND datname = '{}'".format(database))
        cur.execute("DROP DATABASE IF EXISTS {} ;".format(database))
    except Exception as e:
        print(e)
        exit(1)
    cur.execute("CREATE DATABASE {} ;".format(database))
    cur.execute("GRANT ALL PRIVILEGES ON DATABASE {} TO {} ;".format(database, user_name))
    return database

#restore_database - restored db name (tenantapptest_restore)
#new_active_database - original db name (tenantapptest)
def swap_after_restore(db_host, restore_database, new_active_database, db_port, user_name, user_password):
    try:
        con = psycopg2.connect(dbname='postgres', port=db_port,
                               user=user_name, host=db_host,
                               password=user_password)
        con.set_isolation_level(ISOLATION_LEVEL_AUTOCOMMIT)
        cur = con.cursor()

        logger = logging.getLogger(__name__)
        logger.info(f"swap_after_restore: pg_terminate_backend (disconnects a session from the original database {new_active_database})")

        cur.execute("SELECT pg_terminate_backend( pid ) "
                    "FROM pg_stat_activity "
                    "WHERE pid <> pg_backend_pid( ) "
                    "AND datname = '{}'".format(new_active_database))

        logger.info(f"swap_after_restore: DROP DATABASE {new_active_database}")
        cur.execute("DROP DATABASE IF EXISTS {}".format(new_active_database))

        logger.info(f"swap_after_restore: RENAME DATABASE {restore_database} to {new_active_database}")
        cur.execute('ALTER DATABASE "{}" RENAME TO "{}";'.format(restore_database, new_active_database))
    except Exception as e:
        print(e)
        exit(1)


def move_to_local_storage(comp_file, filename_compressed, manager_config):
    """ Move compressed backup into {LOCAL_BACKUP_PATH}. """
    backup_folder = manager_config.get('LOCAL_BACKUP_PATH')
    try:
        check_folder = os.listdir(backup_folder)
    except FileNotFoundError:
        os.mkdir(backup_folder)
    shutil.move(comp_file, '{}{}'.format(manager_config.get('LOCAL_BACKUP_PATH'), filename_compressed))


def main():

    #Testing
    # Get the directory of the current script
    # script_dir = os.path.dirname(os.path.abspath(__file__))
    # # Navigate up two levels and into 'orderflex/config'
    # yaml_path = os.path.join(script_dir, '..', '..', 'orderflex', 'config', 'parameters.yml')
    # # Normalize the path
    # yaml_path = os.path.normpath(yaml_path)
    # print("YAML path:", yaml_path)
    # exit(1)

    logger = logging.getLogger(__name__)
    logger.setLevel(logging.INFO)
    handler = logging.StreamHandler()
    formatter = logging.Formatter('%(asctime)s - %(name)s - %(levelname)s - %(message)s')
    handler.setFormatter(formatter)
    logger.addHandler(handler)

    args_parser = argparse.ArgumentParser(description='Postgres database management')
    args_parser.add_argument("--action",
                             metavar="action",
                             choices=['list', 'list_dbs', 'restore', 'backup'],
                             required=True)
    args_parser.add_argument("--date",
                             metavar="YYYY-MM-dd",
                             help="Date to use for restore (show with --action list)")
    args_parser.add_argument("--dest-db",
                             metavar="dest_db",
                             default=None,
                             help="Name of the new restored database")
    args_parser.add_argument("--verbose",
                             default=False,
                             help="Verbose output")
    #args_parser.add_argument("--configfile",
    #                         help="Database configuration file")
    args_parser.add_argument("--path",
                             default=False,
                             help="Destination path to overwrite config's path (i.e. /srv/order-lab-tenantapp1/orderflex/var/backups/)")
    args_parser.add_argument("--prefix",
                             default=False,
                             help="Prefix attach to the backup filename 'backup-prefix...'")
    args_parser.add_argument("--source-db",
                             metavar="source_db",
                             default=False,
                             help="Name of the source database to overwrite config's db")
    args_parser.add_argument("--user",
                             default=False,
                             help="DB username")
    args_parser.add_argument("--password",
                             default=False,
                             help="DB password")

    args = args_parser.parse_args()

    #Get DB parameters from db.config file passed by --configfile (default location: /postgres-manage-python/)
    #use config/paramaters.yml, instead of db.config
    # config = configparser.ConfigParser()
    # config.read(args.configfile)
    #
    # postgres_host = config.get('postgresql', 'host')
    # postgres_port = config.get('postgresql', 'port')
    # postgres_db = config.get('postgresql', 'db')
    # postgres_restore = "{}_restore".format(postgres_db)
    # postgres_user = config.get('postgresql', 'user')
    # postgres_password = config.get('postgresql', 'password')
    # aws_bucket_name = config.get('S3', 'bucket_name')
    # aws_bucket_path = config.get('S3', 'bucket_backup_path')
    # storage_engine = config.get('setup', 'storage_engine')

    #current_folder = os.getcwd()
    #yaml_path = os.path.join(current_folder, 'parameters.yml')
    # Get the directory of the current script
    script_dir = os.path.dirname(os.path.abspath(__file__))
    # Navigate up two levels and into 'orderflex/config'
    yaml_path = os.path.join(script_dir, '..', '..', '..', 'orderflex', 'config', 'parameters.yml')
    # Normalize the path
    yaml_path = os.path.normpath(yaml_path)
    print("YAML path:", yaml_path)

    with open(yaml_path, 'r') as file:
        content = yaml.safe_load(file)

    params = content.get('parameters', {})
    postgres_host = params.get('database_host')
    postgres_port = params.get('database_port')
    postgres_db = params.get('database_name')
    postgres_user = params.get('database_user')
    postgres_password = params.get('database_password')
    storage_engine = 'LOCAL'
    print(f"postgres_host={postgres_host}, postgres_port={postgres_port}, postgres_db={postgres_db}, postgres_user={postgres_user}, postgres_password={postgres_password}")
    #exit(1)

    #local_storage_path = config.get('local_storage', 'path', fallback='./backups/')

    timestr = datetime.datetime.now().strftime('%Y%m%d-%H%M%S')
    filename = 'backup-{}-{}.dump'.format(timestr, postgres_db)
    filename_compressed = '{}.gz'.format(filename)
    restore_filename = '/tmp/restore.dump.gz'
    restore_uncompressed = '/tmp/restore.dump'

    if args.path:
        local_storage_path = args.path

    if args.prefix:
        prefix = args.prefix
    else:
        prefix = 'unknownenv'

    if args.user:
        postgres_user = args.user
    if args.password:
        postgres_password = args.password

    if args.source_db:
        postgres_db = args.source_db
        postgres_restore = "{}_restore".format(postgres_db)
        filename = 'backupdb-{}-{}-{}.dump'.format(prefix, timestr, postgres_db)
        filename_compressed = '{}.gz'.format(filename)

    print("path=",local_storage_path)
    log_file_path = os.path.join(local_storage_path, 'pythondb.log')
    # Check if the file exists
    if not os.path.exists(log_file_path):
        #os.makedirs(os.path.dirname(log_file_path), exist_ok=True)
        # Create the file
        with open(log_file_path, 'w') as f:
            f.write('manage db log')  # Optionally write an initial line
        print("Log file created:", log_file_path)
    else:
        print("Log file already exists:", log_file_path)

    logging.basicConfig(filename=local_storage_path+"pythondb.log")
    #print("logger=", logging.getLoggerClass().root.handlers[0].baseFilename)

    logger.info('Source database name postgres_db={}'.format(postgres_db))

    manager_config = {
        #'AWS_BUCKET_NAME': aws_bucket_name,
        #'AWS_BUCKET_PATH': aws_bucket_path,
        'BACKUP_PATH': local_storage_path+'tmp/',
        'LOCAL_BACKUP_PATH': local_storage_path
    }

    local_file_path = '{}{}'.format(manager_config.get('BACKUP_PATH'), filename)

    # list task
    if args.action == "list":
        backup_objects = sorted(list_available_backups(storage_engine, manager_config), reverse=True)
        for key in backup_objects:
            logger.info("Key : {}".format(key))
    # list databases task
    elif args.action == "list_dbs":
        result = list_postgres_databases(postgres_host,
                                         postgres_db,
                                         postgres_port,
                                         postgres_user,
                                         postgres_password)
        for line in result.splitlines():
            logger.info(line)
    # backup task
    elif args.action == "backup":
        logger.info('Backing up {} database to {}'.format(postgres_db, local_file_path))
        result = backup_postgres_db(postgres_host,
                                    postgres_db,
                                    postgres_port,
                                    postgres_user,
                                    postgres_password,
                                    local_file_path,
                                    args.verbose
                                    )
        if args.verbose:
            for line in result.splitlines():
                logger.info(line)

        logger.info("Backup complete")
        #print("Backup complete.")
        logger.info("Compressing {}".format(local_file_path))
        comp_file = compress_file(local_file_path)
        # Delete the original file after compression
        os.remove(local_file_path)

        if storage_engine == 'LOCAL':
            logger.info('Moving {} to local storage...'.format(comp_file))
            move_to_local_storage(comp_file, filename_compressed, manager_config)
            movedmsg = "Moved to {}{}".format(manager_config.get('LOCAL_BACKUP_PATH'), filename_compressed);
            #logger.info("Moved to {}{}".format(manager_config.get('LOCAL_BACKUP_PATH'), filename_compressed))
            logger.info(movedmsg)
            movedmsg = "Backup file has been created: {}".format(filename_compressed);
            print(movedmsg)
        elif storage_engine == 'S3':
            logger.info('Uploading {} to Amazon S3...'.format(comp_file))
            upload_to_s3(comp_file, filename_compressed, manager_config)
            logger.info("Uploaded to {}".format(filename_compressed))
    # restore task
    elif args.action == "restore":
        if not args.date:
            logger.warn('No date was chosen for restore. Run again with the "list" '
                        'action to see available restore dates')
        else:
            try:
                os.remove(restore_filename)
            except Exception as e:
                logger.info(e)
            all_backup_keys = list_available_backups(storage_engine, manager_config)
            backup_match = [s for s in all_backup_keys if args.date in s]
            if backup_match:
                logger.info("Found the following backup : {}".format(backup_match))
            else:
                logger.error("No match found for backups with date : {}".format(args.date))
                logger.info("Available keys : {}".format([s for s in all_backup_keys]))
                exit(1)

            if storage_engine == 'LOCAL':
                logger.info("Choosing {} from local storage".format(backup_match[0]))
                shutil.copy('{}/{}'.format(manager_config.get('LOCAL_BACKUP_PATH'), backup_match[0]),
                            restore_filename)
                logger.info("Fetch complete")
            elif storage_engine == 'S3':
                logger.info("Downloading {} from S3 into : {}".format(backup_match[0], restore_filename))
                download_from_s3(backup_match[0], restore_filename, manager_config)
                logger.info("Download complete")

            logger.info("Extracting {}".format(restore_filename))
            ext_file = extract_file(restore_filename)
            # cleaned_ext_file = remove_faulty_statement_from_dump(ext_file)
            logger.info("Extracted to : {}".format(ext_file))
            logger.info("Creating temp database for restore : {}".format(postgres_restore))
            tmp_database = create_db(postgres_host,
                                     postgres_restore,
                                     postgres_port,
                                     postgres_user,
                                     postgres_password)
            logger.info("Created temp database for restore : {}".format(tmp_database))
            logger.info("Restore starting")
            result = restore_postgres_db(postgres_host,
                                         postgres_restore,
                                         postgres_port,
                                         postgres_user,
                                         postgres_password,
                                         restore_uncompressed,
                                         args.verbose)
            if args.verbose:
                for line in result.splitlines():
                    logger.info(line)
            logger.info("Restore complete")
            if args.dest_db is not None:
                restored_db_name = args.dest_db
                restoremsg = "Switching restored database with new one : {} > {}".format(
                    postgres_restore, restored_db_name
                )
                #logger.info("Switching restored database with new one : {} > {}".format(
                #    postgres_restore, restored_db_name
                #))
                logger.info(restoremsg)
                print(restoremsg)
            else:
                restored_db_name = postgres_db
                restoremsg = "Switching restored database with active one : {} > {}".format(
                    postgres_restore, restored_db_name
                )
                #logger.info("Switching restored database with active one : {} > {}".format(
                #    postgres_restore, restored_db_name
                #))
                logger.info(restoremsg)
                print(restoremsg)

            swap_after_restore(postgres_host,
                               postgres_restore, #restored db name (tenantapptest_restore)
                               restored_db_name, #original db name (tenantapptest)
                               postgres_port,
                               postgres_user,
                               postgres_password)
            logger.info("Database restored and active.")
            print("Database restored and active.")
    else:
        logger.warn(f"No valid argument was given. action={args.action}")
        logger.warn(args)


if __name__ == '__main__':
    main()
