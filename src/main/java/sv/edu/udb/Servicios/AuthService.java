package sv.edu.udb.Servicios;

import sv.edu.udb.Modelos.User;
import sv.edu.udb.Repositorios.UserRepository;
import org.springframework.stereotype.Service;
import org.springframework.security.crypto.password.PasswordEncoder;

@Service
public class AuthService {
    private final UserRepository userRepository;
    private final PasswordEncoder passwordEncoder;

    public AuthService(UserRepository userRepository, PasswordEncoder passwordEncoder) {
        this.userRepository = userRepository;
        this.passwordEncoder = passwordEncoder;
    }

    public User registrarUsuario(User user) {
        // Encriptar contraseña obligatoriamente (Regla de evaluación [cite: 34])
        user.setPassword(passwordEncoder.encode(user.getPassword()));
        return userRepository.save(user);
    }

    // Aquí Adrian va la lógica de Login y tambien el codigo para la generacion de JWT
}