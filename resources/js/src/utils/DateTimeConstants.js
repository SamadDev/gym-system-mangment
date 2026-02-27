export default class DateTimeConstants {
    /**
     * Default timezone (used if device timezone can't be detected)
     */
    static DEFAULT_TIMEZONE = 'Asia/Baghdad';

    /**
     * Default format for date (e.g. 2025-06-13)
     */
    static DATE_FORMAT = 'YYYY-MM-DD';

    /**
     * Default format for date & time (e.g. 2025-06-13 03:30 PM)
     */
    static DATETIME_FORMAT = 'YYYY-MM-DD hh:mm A';

    /**
     * Get device timezone, fallback to default if unavailable
     */
    static getTimezone() {
        try {
            const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
            return tz || this.DEFAULT_TIMEZONE;
        } catch (error) {
            return this.DEFAULT_TIMEZONE;
        }
    }
}
