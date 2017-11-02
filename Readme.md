# Docker Node/Composer Image
This image is based on the narwhaldigital:node image and adds the following:
* PHP7
* composer
* curl
* wget

## Building Image
From the repo directory run the following:
`docker build -t narwhaldigital/node-composer .`

Once built, push using `docker push narwhaldigital/node-composer:latest`
