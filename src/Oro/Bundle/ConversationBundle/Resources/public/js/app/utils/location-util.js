import moment from 'moment';
import dateTimeFormatter from 'orolocale/js/formatter/datetime';

export const updateLocationWithParams = (params = {}) => {
    const url = new URL(location);
    const search = new URLSearchParams(url.search);

    for (const [paramName, paramValue] of Object.entries(params)) {
        if (!paramValue && search.has(paramName)) {
            search.delete(paramName);
        } else {
            paramValue && search.set(paramName, paramValue);
        }
    }

    url.search = search.toString();
    history.pushState(null, '', url.toString());
};

export const getLocationParams = () => {
    const url = new URL(location);
    return new URLSearchParams(url.search);
};

export const getSelectedFromLocation = ({paramName = 'conversation_id', defaultValue = null} = {}) => {
    const params = getLocationParams();

    if (params.get(paramName)) {
        return params.get(paramName);
    }

    return defaultValue;
};

export const isTodayDate = date => {
    if (!date) {
        return false;
    }

    const todayMoment = moment().tz(dateTimeFormatter.timezone);
    return dateTimeFormatter.formatDate(date) === todayMoment.format(dateTimeFormatter.getDateFormat());
};

export const scrollToElementIntoView = (element, options = {}) => {
    if ('scrollIntoViewIfNeeded' in element) {
        return element.scrollIntoViewIfNeeded(options);
    }

    element.scrollIntoView(options);
};
