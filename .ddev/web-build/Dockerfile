# Cypress.io requirements
# https://docs.cypress.io/guides/continuous-integration/introduction#Dependencies

ARG BASE_IMAGE
FROM $BASE_IMAGE

# Given that ddev has installed python3-pip, we can grab yq.
RUN pip3 install yq

#RUN apt update && apt-get install \
#    libgtk2.0-0 libgtk-3-0 libgbm-dev libnotify-dev libgconf-2-4 libnss3 libxss1 libasound2 libxtst6 xauth xvfb \
#    python2  build-essential gcc g++ \
#    -y

# Download chromium manually, because the package name varies between machine.
#RUN apt-get update && apt-get install -y chromium || apt-get install -y chromium-browser
# https://github.com/puppeteer/puppeteer/issues/7981 - If pa11y-ci updates their version of puppeteer to ~13, we can remove this symlink.
#RUN ln -s `which chromium` /usr/bin/chromium-browser
