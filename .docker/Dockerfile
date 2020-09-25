FROM olekhy/pdfizer

ARG APPUID=0
ARG APPUGID=0

# we setup an user id and group id identiclay to user on host
# and don't run in permission troubles
RUN if [ ${APPUID} != 0 ]; then \
        groupadd -g ${APPUGID} waglpz;    \
        useradd -r -l -u ${APPUID} -g waglpz waglpz; \
    fi;

RUN chown -R waglpz:waglpz $COMPOSER_HOME
