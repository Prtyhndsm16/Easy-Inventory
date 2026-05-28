- [ ] Gather understanding of the current request error cause (context canceled) by identifying outbound network call sites.
- [ ] Fix bug in CashieringController::checkout(): initialize $stockChanges before DB::transaction loop.
- [ ] Make low-stock email notification non-blocking (queue) or otherwise ensure user flow isn’t affected by mail delivery failures.
- [ ] Add detailed logging in LowStockNotifier around triggered products and mail transport errors.
- [x] Validate product image visibility issue by instrumenting image rendering and logging (check DB image_path vs /storage URL building).
- [ ] Run existing automated tests (phpunit) and manually smoke-test cashiering + stock-out flows.


