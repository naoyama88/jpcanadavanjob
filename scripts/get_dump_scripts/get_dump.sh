# e.g.
# sh ~/workspace/jpcanadavanjob/scripts/get_dump_scripts/get_dump.sh 2020_05_14

DATE=$1

heroku pg:backups:capture -a jpcanadavanjob
heroku pg:backups:download -o ./ignore/$DATE.dump -a jpcanadavanjob
