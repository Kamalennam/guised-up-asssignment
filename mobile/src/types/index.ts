export type ApiError = {
  message: string;
  errors?: Record<string, string[]>;
};

export type PaginatedMeta = {
  page: number;
  per_page: number;
  has_more: boolean;
};
