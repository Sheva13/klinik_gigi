# Implementasi Biaya Layanan di Flutter

## Deskripsi
Implementasi ini menunjukkan cara mengambil biaya layanan dari backend Laravel ke aplikasi Flutter, menggantikan biaya yang sebelumnya di-hardcode.

## Endpoint API
Endpoint untuk mengambil biaya layanan:
```
POST /api/biaya-layanan/get-biaya
```

Parameter yang diperlukan:
- `tipe_layanan`: Jenis layanan ('klinik', 'homecare', 'online')
- `jenis_pasien`: Jenis pasien ('Umum', 'BPJS', 'Asuransi')

## Contoh Implementasi di Flutter

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class BiayaLayananService {
  static const String baseUrl = 'http://your-api-url/api';

  // Fungsi untuk mengambil biaya layanan dari backend
  Future<double?> getBiayaLayanan(String tipeLayanan, String jenisPasien) async {
    final url = Uri.parse('$baseUrl/biaya-layanan/get-biaya');
    
    try {
      final response = await http.post(
        url,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          // Tambahkan header autentikasi jika diperlukan
          // 'Authorization': 'Bearer $token',
        },
        body: jsonEncode({
          'tipe_layanan': tipeLayanan,
          'jenis_pasien': jenisPasien,
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success']) {
          return data['data']['biaya_reservasi'].toDouble();
        } else {
          print('Gagal mengambil biaya: ${data['message']}');
          return null;
        }
      } else {
        print('Gagal menghubungi server: ${response.statusCode}');
        return null;
      }
    } catch (e) {
      print('Error saat mengambil biaya: $e');
      return null;
    }
  }
}

// Contoh penggunaan di widget
class BiayaDisplayWidget extends StatefulWidget {
  final String tipeLayanan;
  final String jenisPasien;

  const BiayaDisplayWidget({
    Key? key,
    required this.tipeLayanan,
    required this.jenisPasien,
  }) : super(key: key);

  @override
  _BiayaDisplayWidgetState createState() => _BiayaDisplayWidgetState();
}

class _BiayaDisplayWidgetState extends State<BiayaDisplayWidget> {
  double? biaya;
  bool isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadBiaya();
  }

  Future<void> _loadBiaya() async {
    setState(() {
      isLoading = true;
    });

    final biayaLayananService = BiayaLayananService();
    final result = await biayaLayananService.getBiayaLayanan(
      widget.tipeLayanan,
      widget.jenisPasien,
    );

    setState(() {
      biaya = result;
      isLoading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    if (isLoading) {
      return const CircularProgressIndicator();
    }

    if (biaya == null) {
      return const Text('Biaya tidak ditemukan');
    }

    return Text(
      'Biaya: Rp ${biaya!.toInt().toString().replaceAllMapped(RegExp(r'(\d)(?=(\d{3})+(?!\d))'), (Match m) => '${m[1]},')}',
      style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
    );
  }
}
```

## Contoh Penggunaan di Form Reservasi

```dart
// Di form reservasi, sebelum membuat reservasi
class ReservasiForm extends StatefulWidget {
  @override
  _ReservasiFormState createState() => _ReservasiFormState();
}

class _ReservasiFormState extends State<ReservasiForm> {
  String selectedTipeLayanan = 'klinik';
  String selectedJenisPasien = 'Umum';
  double? biayaReservasi;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        // Dropdown untuk tipe layanan
        DropdownButton<String>(
          value: selectedTipeLayanan,
          items: const [
            DropdownMenuItem(value: 'klinik', child: Text('Klinik')),
            DropdownMenuItem(value: 'homecare', child: Text('Home Care')),
            DropdownMenuItem(value: 'online', child: Text('Online')),
          ],
          onChanged: (value) {
            if (value != null) {
              setState(() {
                selectedTipeLayanan = value;
              });
              _updateBiaya();
            }
          },
        ),
        
        // Dropdown untuk jenis pasien
        DropdownButton<String>(
          value: selectedJenisPasien,
          items: const [
            DropdownMenuItem(value: 'Umum', child: Text('Umum')),
            DropdownMenuItem(value: 'BPJS', child: Text('BPJS')),
            DropdownMenuItem(value: 'Asuransi', child: Text('Asuransi')),
          ],
          onChanged: (value) {
            if (value != null) {
              setState(() {
                selectedJenisPasien = value;
              });
              _updateBiaya();
            }
          },
        ),
        
        // Tampilkan biaya yang diambil dari backend
        if (biayaReservasi != null)
          Text('Biaya: Rp ${biayaReservasi!.toInt()}')
        else
          const Text('Memuat biaya...'),
        
        ElevatedButton(
          onPressed: () {
            // Proses reservasi dengan biaya dari backend
            _buatReservasi();
          },
          child: const Text('Buat Reservasi'),
        ),
      ],
    );
  }

  Future<void> _updateBiaya() async {
    final service = BiayaLayananService();
    final biaya = await service.getBiayaLayanan(
      selectedTipeLayanan,
      selectedJenisPasien,
    );
    
    setState(() {
      biayaReservasi = biaya;
    });
  }

  void _buatReservasi() {
    // Kirim data ke endpoint pembuatan reservasi
    // Biaya sudah otomatis diambil dari backend
  }
}
```

## Keuntungan dari Pendekatan Ini

1. **Fleksibilitas**: Biaya dapat diubah tanpa perlu update aplikasi
2. **Konsistensi**: Semua biaya dikelola dari satu sumber (backend)
3. **Skalabilitas**: Mudah menambahkan kombinasi tipe layanan dan jenis pasien baru
4. **Keamanan**: Logika bisnis tetap di backend, tidak bisa diubah dari sisi client
```
