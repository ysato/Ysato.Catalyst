FROM shivammathur/node:latest

RUN apt-get update && apt-get install -y ca-certificates
COPY ./certs/certificate.pem /usr/local/share/ca-certificates/certificate.crt
RUN update-ca-certificates
