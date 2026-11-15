export interface Item {
  id: string;
  payload: {
    question: string;
    options: string[];
    answer: number;
    explanation: string;
    sources: {
      citation: string;
      url?: string;
    }[];
  };
}

export interface ReviewResponse {
  items: Item[];
}

export interface AnswerResponse {
  status: "processed" | "already_processed";
  data: {
    next_review_at?: string;
    state?: Record<string, unknown>;
  };
}
