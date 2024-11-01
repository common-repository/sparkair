export const createShortcode = (
  tag: string,
  attributes: Record<string, string | number>
) => {
  return `[${tag} ${Object.entries(attributes)
    .map(([key, value]) => `${key}="${value}"`)
    .join(" ")}][/${tag}]`;
};

export default {
  createShortcode,
};
