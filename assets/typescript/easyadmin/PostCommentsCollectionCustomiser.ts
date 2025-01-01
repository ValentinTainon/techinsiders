export default class PostCommentsCollectionCustomiser {
  public removeAllowDeleteIfUnauthorizedUser(
    postCommentsCollection: NodeListOf<HTMLDivElement>
  ): void {
    postCommentsCollection.forEach((item) => {
      const divWithDataAllowDeleteItem = item.querySelector<HTMLDivElement>(
        "div[data-allow-delete-item]"
      );

      if (!divWithDataAllowDeleteItem) return;

      const deleteButton = item.querySelector<HTMLButtonElement>(
        "button.field-collection-delete-button"
      );

      if (!deleteButton) return;

      if (divWithDataAllowDeleteItem.dataset.allowDeleteItem === "false") {
        deleteButton.remove();
      }
    });
  }
}
