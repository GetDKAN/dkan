# This file is to help with running behat tests on circleCI. It's needed to:
#  - Handle circleCI's parallelization.
#  - Handle searching in multiple directories for feature files.
# Ex. There are 3 VMs setup: (3)

# TODO: remove by end of month (9/30/2016)
echo "*** DEPRECATED ***"
echo "*** use .ahoy/.scripts/circle-behat.rb ***"
echo "*** DEPRECATED ***"
echo "*** use .ahoy/.scripts/circle-behat.rb ***"
echo "*** DEPRECATED ***"
echo "*** use .ahoy/.scripts/circle-behat.rb ***"

if [ -z "$CIRCLE_NODE_INDEX" ]; then
  echo "No parrallelism found, setting defaults to run all tests."
  CIRCLE_NODE_TOTAL=1
  CIRCLE_NODE_INDEX=0
fi

echo "\$CIRCLE_NODE_TOTAL = $CIRCLE_NODE_TOTAL"
echo "\$CIRCLE_NODE_INDEX = $CIRCLE_NODE_INDEX"

error=0
pwd=$(pwd)
declare -a files
declare -a params
# Fetch all of the feature files for each parameter (directories)
for param in "$@"; do
    echo "Seaching $param for feature files..."
    if [ -d "$param" ]; then
      while read -r entry; do
        echo "-- found $entry"
        files=(${files[@]} "$entry")
        # Bash wizardry so we can update the array (otherwise it's a subprocess)
      done < <(find $param | grep "\.feature$")
    else
      # Fetch all the params to be passed later to behat
      # Quote named params to allow special chars inside
      # them. This allow us to use && || or any other
      # reserved operand inside params.
      if [[ $param == *"="* ]]
      then
        arrParam=(${param//=/ })
        param="${arrParam[0]}='${arrParam[1]}'"
      fi
      echo "Fetch param $param"
      params=(${params[@]} "$param")
    fi
done

for i in "${!files[@]}"; do
    if [ $(($i % $CIRCLE_NODE_TOTAL)) -eq $CIRCLE_NODE_INDEX ]; then
      echo "Running ahoy dkan test `pwd`/${files[$i]} --format=pretty --out=std --format=junit --out=$CIRCLE_ARTIFACTS/junit ${params} --colors"
      time ahoy dkan test `pwd`/${files[$i]} --format=pretty --out=std --format=junit --out=$CIRCLE_ARTIFACTS/junit ${params} --colors
    fi
    # Mark the entire script as a failure if any of the iterations fail.
    if [ ! $? -eq 0 ]
    then
      error=1
    fi
done
exit $error
