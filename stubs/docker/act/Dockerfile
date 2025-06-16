FROM shivammathur/node:latest
{% if with_ca is not null %}

RUN apt-get update && apt-get install -y ca-certificates
COPY {{ with_ca }} /usr/local/share/ca-certificates/certificate.crt
RUN update-ca-certificates
{% endif %}
