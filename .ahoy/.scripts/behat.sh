echo "*** DEPRECATED ***"
echo "*** DEPRECATED ***"
echo "*** DEPRECATED ***"
echo "*** DEPRECATED ***"
echo "Use .scripts/dkan-test.sh"

cd $1
shift
echo $*
bin/behat $*
