# e.g.
# sh ~/workspace/jpcanadavanjob/scripts/db_backup_scripts/create_csv_script.sh 20200501 20200514

DIR=~/workspace/jpcanadavanjob
START=$1
END=$2

# house

# run SQL and store it on csv file
heroku pg:psql -c " `php $DIR/scripts/db_backup_scripts/create_sql.php $START $END houses` "  -a jpcanadavanjob > $DIR/ignore/csv/house-$START-$END.csv
# delete line 2, 4, 5
sed -i -e '2d;4d;5d;' $DIR/ignore/csv/house-$START-$END.csv
# delete space
sed -i -e 's/ //g' $DIR/ignore/csv/house-$START-$END.csv
# convert "|" to "," to make file csv
sed -i -e 's/|/,/g' $DIR/ignore/csv/house-$START-$END.csv


# job
heroku pg:psql -c " `php $DIR/scripts/db_backup_scripts/create_sql.php $START $END jobs` "  -a jpcanadavanjob > $DIR/ignore/csv/job-$START-$END.csv
sed -i -e '2d;4d;5d;' $DIR/ignore/csv/job-$START-$END.csv
sed -i -e 's/ //g' $DIR/ignore/csv/job-$START-$END.csv
sed -i -e 's/|/,/g' $DIR/ignore/csv/job-$START-$END.csv


#sell & buy
heroku pg:psql -c " `php $DIR/scripts/db_backup_scripts/create_sql.php $START $END items` "  -a jpcanadavanjob > $DIR/ignore/csv/buy-$START-$END.csv
sed -i -e '2d;4d;5d;' $DIR/ignore/csv/buy-$START-$END.csv
sed -i -e 's/ //g' $DIR/ignore/csv/buy-$START-$END.csv
sed -i -e 's/|/,/g' $DIR/ignore/csv/buy-$START-$END.csv


# delete files that stores strings before comverted
rm -f $DIR/ignore/csv/house-$START-$END.csv-e
rm -f $DIR/ignore/csv/job-$START-$END.csv-e
rm -f $DIR/ignore/csv/buy-$START-$END.csv-e

