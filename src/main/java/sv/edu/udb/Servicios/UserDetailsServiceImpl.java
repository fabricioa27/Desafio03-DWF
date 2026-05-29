package sv.edu.udb.Servicios;

import lombok.AllArgsConstructor;
import org.springframework.security.core.userdetails.UserDetails;
import org.springframework.security.core.userdetails.UserDetailsService;
import org.springframework.security.core.userdetails.UsernameNotFoundException;
import org.springframework.stereotype.Service;
import sv.edu.udb.Configuraciones.UserDetailsImpl;
import sv.edu.udb.Modelos.User;
import sv.edu.udb.Repositorios.UserRepository;

//Clase que implementa UserDetailsService para mejor integracion con el entorno de Spring Security
// Carga desde la BD el usuario y lo convierte a UserDetails
@AllArgsConstructor
@Service
public class UserDetailsServiceImpl implements UserDetailsService {

    private final UserRepository userRepository;

    @Override
    public UserDetails loadUserByUsername(String username) throws UsernameNotFoundException {
        User usuario = userRepository.findByUsername(username).orElseThrow(() -> new RuntimeException("Usuario no encontrado"));
        return new UserDetailsImpl(usuario);
    }
}
