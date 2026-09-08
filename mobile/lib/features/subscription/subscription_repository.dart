import '../../core/models/subscription.dart';
import '../../core/network/api_client.dart';

/// Talking to Safaricom, at one remove.
///
/// The app never handles money. It asks the server to put a prompt on a phone,
/// then asks whether the callback has arrived. It has no way to say a payment
/// succeeded, which is the whole point: a modified app cannot buy itself a
/// subscription.
class SubscriptionRepository {
  SubscriptionRepository({required this.api});

  final ApiClient api;

  Future<SubscriptionState> load() async {
    return SubscriptionState.fromJson(await api.get('/subscription'));
  }

  /// Ask for the M-Pesa prompt. Returns the id to poll with.
  Future<String> requestPayment({required String phone, required String planType}) async {
    final response = await api.post('/subscription/stk-push', body: {
      'phone_number': phone,
      'plan_type': planType,
    });

    return response['checkout_request_id'] as String? ?? '';
  }

  Future<PaymentStatus> check(String checkoutRequestId) async {
    return PaymentStatus.fromJson(await api.get('/subscription/status/$checkoutRequestId'));
  }
}
