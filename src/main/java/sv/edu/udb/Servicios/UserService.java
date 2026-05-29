package sv.edu.udb.Servicios;

import lombok.AllArgsConstructor;
import sv.edu.udb.Modelos.User;
import sv.edu.udb.Repositorios.UserRepository;
import org.springframework.stereotype.Service;
import java.util.List;

@AllArgsConstructor
@Service
public class UserService {
    private final UserRepository userRepository;

    public List<User> listarUsuarios() {
        return userRepository.findAll();
    }

    public boolean verificarUsuarioExiste(String username) {
        return userRepository.existsByUsername(username);
    }

    public User registrarUsuario(User user) {
        return userRepository.save(user);
    }
}