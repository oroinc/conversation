export const patchSyncOptions = options => {
    if (!options.contentType) {
        options.contentType = 'application/vnd.api+json';
    }

    options.beforeSend = function(xhr) {
        xhr.setRequestHeader('Accept', 'application/vnd.api+json');
        xhr.setRequestHeader('X-Include', 'noHateoas');
    };

    return options;
};

export const patchReceivedData = received => {
    if (received.hasOwnProperty('data')) {
        received = received.data;

        if (Array.isArray(received)) {
            received = received.map(patchReceivedData);
        }
    }

    if (received.hasOwnProperty('attributes')) {
        Object.assign(received, received.attributes);

        delete received.attributes;
    }

    return received;
};
