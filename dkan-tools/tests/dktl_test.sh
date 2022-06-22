#! /bin/sh
# file: tests/dktl_test.sh
#
# To run from this directory:
#
# wget https://github.com/kward/shunit2/archive/v2.1.8.tar.gz
# mkdir shunit2 && tar xf v2.1.8.tar.gz -C shunit2 --strip-components 1
# ./dktl_test.sh

# Tests will break if name contains non-alphanumeric characters!
export DKTL_TEST_PROJECT_NAME='testproject'
export DKTL_TEST_PROJECT_DIR="/tmp/${DKTL_TEST_PROJECT_NAME}"
export DKTL_TEST_DIR=`pwd`

oneTimeSetUp() {
    mkdir $DKTL_TEST_PROJECT_DIR
    chmod 755 $DKTL_TEST_PROJECT_DIR
}

setUp() {
    cd $DKTL_TEST_PROJECT_DIR
}

testUninitialized() {
    result=`dktl`
    assertContains "${result}" "DKTL is running outside of a DKTL project."
}

testDktlInitWithBadParameter() {
    result=`dktl init --dkan=foobar`
    assertContains "${result}" "Could not parse version constraint foobar"
    rm -rf composer.* dktl.yml docrot src
}

testDktlInit() {
    result=`dktl init`
    assertContains "${result}" 'Composer project created'

    result=`ls`
    assertContains "${result}" "dktl.yml"
    assertContains "${result}" "src"
    assertContains "${result}" "composer.json"
    assertContains "${result}" "docroot"
    assertNotContains "${result}" "composer.lock"
    assertNotContains "${result}" "vendor"

    result=`ls src`
    assertContains "${result}" "command"
    assertContains "${result}" "docker"
    assertContains "${result}" "libraries"
    assertContains "${result}" "modules"
    assertContains "${result}" "script"
    assertContains "${result}" "site"
    assertContains "${result}" "test"
    assertContains "${result}" "themes"
}

testDktlMake() {
    result=`dktl make`
    assertContains "${result}" "Installing drupal/core"
    assertContains "${result}" "Installing getdkan/dkan"
    result=`ls docroot/modules/contrib`
    assertContains "${result}" "dkan"
}

testDktlInstall() {
    result=`dktl install`
    assertContains "${result}" "Performed install task: install_finished"
    url=`dktl url`
    result=`curl $url/api/1`
    assertContains "API" "${result}" "openapi"
    result=`curl $url/user/login`
    assertContains "${result}" "Enter your DKAN username"
    result=`dktl install:sample`
    assertContains "${result}" "Processed 10 items from the datastore_import"
}

testFrontEnd() {
    result=`dktl frontend:install`
    assertContains "${result}" "Successfully symlinked /src/frontend to docroot/frontend"
    assertContains "${result}" "Successfully symlinked global cypress into frontend folder"
    assertContains "${result}" "Front-end dependencies installed."
    assertContains "${result}" "Successfully installed and enabled front-end theme"

    result=`ls src/frontend`
    assertContains "${result}" "package.json"

    result=`dktl frontend:build`
    assertContains "${result}" "The project was built assuming it is hosted at /frontend/build/."

    result=`curl $url`
    assertContains "${result}" '<div id="root">'
}

testBringDown() {
    dktl down

    result=`docker ps --all --filter "name=${DKTL_TEST_PROJECT_NAME}"`
    assertNotContains "${result}" "${DKTL_TEST_PROJECT_NAME}_cli_1"
    assertNotContains "${result}" "${DKTL_TEST_PROJECT_NAME}_web_1"
    assertNotContains "${result}" "${DKTL_TEST_PROJECT_NAME}_web_1"

    result=`docker volume ls --filter "name=${DKTL_TEST_PROJECT_NAME}"`
    assertNotContains "${result}" "${DKTL_TEST_PROJECT_NAME}_composer"

    result=`docker network ls --filter "name=${DKTL_TEST_PROJECT_NAME}"`
    assertNotContains "${result}" "${DKTL_TEST_PROJECT_NAME}_default"
}

oneTimeTearDown() {
    containers=`docker ps --filter name="${DKTL_TEST_PROJECT_NAME}*" -aq`
    echo $containers
    if [ ! -z "$containers" ]; then
        echo "$containers" | xargs docker stop | xargs docker rm -v
        docker network disconnect "${DKTL_TEST_PROJECT_NAME}_default" dktl-proxy
        docker network rm "${DKTL_TEST_PROJECT_NAME}_default"
    fi
    if [ -d "${DKTL_TEST_PROJECT_DIR}" ]; then
        rm -rf $DKTL_TEST_PROJECT_DIR
    fi
}

# Load shUnit2.
. ./shunit2/shunit2
