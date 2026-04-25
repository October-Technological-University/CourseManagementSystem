import 'package:course_management_frontend/login.dart';
import 'package:flutter/material.dart';
import 'theme.dart';

void main() {
  runApp(const BuildStationApp());
}

class BuildStationApp extends StatelessWidget {
  const BuildStationApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Build Station | Institutional Portal',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.darkTheme,
      home: const LoginPage(),
    );
  }
}

