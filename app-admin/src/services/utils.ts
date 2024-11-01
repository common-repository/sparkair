/**
 * Simple object check.
 * @param item
 * @returns {boolean}
 */
export function isObject(item: any) {
  return item && typeof item === "object" && !Array.isArray(item);
}

/**
 * Deep merge two objects.
 * @param target
 * @param ...sources
 */
export function mergeDeep(target: any, ...sources: any): any {
  if (!sources.length) return target;
  const returnTarget = target;
  const source = sources.shift();

  if (isObject(returnTarget) && isObject(source)) {
    for (const key in source) {
      if (isObject(source[key])) {
        if (!returnTarget[key]) Object.assign(returnTarget, { [key]: {} });
        mergeDeep(returnTarget[key], source[key]);
      } else {
        Object.assign(returnTarget, { [key]: source[key] });
      }
    }
  }

  return mergeDeep(returnTarget, ...sources);
}
