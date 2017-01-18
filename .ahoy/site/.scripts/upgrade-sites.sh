set -e

if [ "$1" = "" ] && [ "$tag" = "" ]; then
  echo "No tag or branch supplied."
  exit 1
fi

if [ "$repos" = "" ]; then
  repos=()
else
  IFS=', ' read -r -a repos <<< "$repos"
fi

if [ "$tag" = "" ]; then
  tag=$1
fi

for name in ${repos[@]}; do
  rm -fR $name
  hub clone nucivic/$name --depth=1

  pushd $name
  branch="update-dkan-$tag"

  exists=$(git show-ref "refs/heads/$branch" &2>/dev/null)
  
  if [ -z "$exists" ]; then
    git checkout -b $branch
  fi

  ahoy build update $tag
  git add -A 
  git commit -m "Update $name data_starter_private to $tag
  
  AC
  ==========
    - [ ] tests pass"


  git push origin $branch --force 2> /dev/null

  hub pull-request -m "Update $name data_starter_private to $tag." 2> /dev/null

  popd
done
