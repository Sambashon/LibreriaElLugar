function normalizeForSearch(value) {
    return String(value ?? '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase();
}

function textIncludesSearch(haystack, needle) {
    const normalizedNeedle = normalizeForSearch(needle);
    if (!normalizedNeedle) return true;
    return normalizeForSearch(haystack).includes(normalizedNeedle);
}
