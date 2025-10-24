// Fetch landlord details
$stmt = $conn->prepare("SELECT name, email, phone, address, city, state, zip, title, about, facebook, twitter, google_plus, linkedin 
                        FROM landlords WHERE id = ?");
$stmt->bind_param("i", $landlord_id);
$stmt->execute();
$landlord = $stmt->get_result()->fetch_assoc();
