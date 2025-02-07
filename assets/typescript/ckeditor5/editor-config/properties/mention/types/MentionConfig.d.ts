export interface MentionConfig {
  mention: {
    feeds: Array<MentionFeed>;
  };
}

export interface MentionFeed {
  marker: string;
  feed: Array<MentionFeedItem>;
}

export interface MentionFeedItem {
  id: string;
  fullname: string;
}
